<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventParticipantController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $participants = $event->participants()->with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Participants retrieved successfully',
            'data' => $participants,
        ]);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if ($event->registration_open && now()->lt($event->registration_open)) {
            return response()->json([
                'success' => false,
                'message' => 'Registration for this event has not opened yet',
            ], 422);
        }

        if ($event->registration_deadline && now()->gt(Carbon::parse($event->registration_deadline)->endOfDay())) {
            return response()->json([
                'success' => false,
                'message' => 'Registration for this event has closed',
            ], 422);
        }

        $result = DB::transaction(function () use ($event, $user) {
            // Serialize concurrent enrollments for this event so the capacity
            // check and the insert cannot interleave.
            Event::whereKey($event->id)->lockForUpdate()->first();

            $existingParticipant = $event->participants()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existingParticipant && $existingParticipant->status !== 'cancelled') {
                return ['outcome' => 'already_enrolled'];
            }

            if ($event->max_participants > 0) {
                $current = $event->participants()->whereIn('status', ['registered', 'attended'])->count();
                if ($current >= $event->max_participants) {
                    return ['outcome' => 'full'];
                }
            }

            if ($existingParticipant && $existingParticipant->status === 'cancelled') {
                $existingParticipant->update([
                    'status' => 'registered',
                    'unique_code' => EventParticipant::generateUniqueCode($event),
                    'cancelled_at' => null,
                ]);

                return ['outcome' => 'reenrolled', 'participant' => $existingParticipant->fresh()];
            }

            return ['outcome' => 'created', 'participant' => $event->participants()->create([
                'user_id' => $user->id,
                'status' => 'registered',
                'unique_code' => EventParticipant::generateUniqueCode($event),
                'cancelled_at' => null,
            ])];
        });

        if ($result['outcome'] === 'already_enrolled') {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this event',
            ], 409);
        }

        if ($result['outcome'] === 'full') {
            return response()->json([
                'success' => false,
                'message' => 'This event has reached its maximum participants',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in event',
            'data' => $result['participant'],
        ], $result['outcome'] === 'reenrolled' ? 200 : 201);
    }

    public function show(Event $event, EventParticipant $participant): JsonResponse
    {
        if ($participant->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Participant retrieved successfully',
            'data' => $participant->load('user'),
        ]);
    }

    public function update(Request $request, Event $event, EventParticipant $participant): JsonResponse
    {
        if ($participant->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['attended'])],
        ]);

        $allowedTransitions = [
            'registered' => ['attended'],
            'attended' => [],
            'absent' => [],
            'cancelled' => [],
        ];

        if (!in_array($validated['status'], $allowedTransitions[$participant->status], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid participant status transition',
                'errors' => [
                    'status' => ['The selected status is invalid for the participant\'s current state.'],
                ],
            ], 422);
        }

        $participant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Participant status updated successfully',
            'data' => $participant,
        ]);
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $participant = $event->participants()->where('user_id', $request->user()->id)->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this event',
            ], 404);
        }

        if ($participant->status === 'cancelled') {
            return response()->json([
                'success' => true,
                'message' => 'Registration already cancelled',
                'data' => $participant,
            ]);
        }

        if ($participant->status !== 'registered') {
            return response()->json([
                'success' => false,
                'message' => 'Only registered participants can cancel registration',
            ], 422);
        }

        $participant->update([
            'status' => 'cancelled',
            'unique_code' => null,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully cancelled registration',
            'data' => $participant->fresh(),
        ]);
    }

    public function myEvents(Request $request): JsonResponse
    {
        $participants = $request->user()
            ->participants()
            ->with(['event.category:id,name,slug', 'event.images'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Enrolled events retrieved successfully',
            'data' => $participants,
        ]);
    }

    public function myCode(Request $request, Event $event): JsonResponse
    {
        $participant = $event->participants()
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this event',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Unique code retrieved successfully',
            'data' => [
                'unique_code' => $participant->unique_code,
                'status' => $participant->status,
            ],
        ]);
    }

    public function checkIn(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $participant = $event->participants()
            ->where('unique_code', strtoupper($validated['code']))
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid check-in code',
            ], 404);
        }

        if ($participant->status === 'attended') {
            return response()->json([
                'success' => false,
                'message' => 'Participant has already checked in',
            ], 409);
        }

        if ($participant->status !== 'registered') {
            return response()->json([
                'success' => false,
                'message' => 'Participant cannot be checked in from current status',
            ], 422);
        }

        $participant->update(['status' => 'attended']);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => $participant->load('user:id,name,email'),
        ]);
    }
}
