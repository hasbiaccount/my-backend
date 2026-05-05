<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        if ($event->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this event',
            ], 409);
        }

        if ($event->max_participants > 0) {
            $confirmed = $event->participants()->where('status', 'confirmed')->count();
            if ($confirmed >= $event->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event has reached its maximum participants',
                ], 422);
            }
        }

        if ($event->registration_open && now()->lt($event->registration_open)) {
            return response()->json([
                'success' => false,
                'message' => 'Registration for this event has not opened yet',
            ], 422);
        }

        if ($event->registration_deadline && now()->gt($event->registration_deadline)) {
            return response()->json([
                'success' => false,
                'message' => 'Registration for this event has closed',
            ], 422);
        }

        $participant = $event->participants()->create([
            'user_id' => $user->id,
            'status' => 'registered',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in event',
            'data' => $participant,
        ], 201);
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
            'status' => ['required', Rule::in(['registered', 'attended', 'absent'])],
        ]);

        $allowedTransitions = [
            'registered' => ['attended', 'absent'],
            'attended'   => [],
            'absent'     => [],
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

        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unenrolled from event',
            'data' => null,
        ]);
    }

    public function myEvents(Request $request): JsonResponse
    {
        $participants = $request->user()->participants()->with('event')->get();

        return response()->json([
            'success' => true,
            'message' => 'Enrolled events retrieved successfully',
            'data' => $participants,
        ]);
    }
}
