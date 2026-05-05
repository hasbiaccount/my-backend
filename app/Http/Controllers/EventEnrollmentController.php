<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventEnrollmentController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $enrollments = $event->enrollments()->with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Enrollments retrieved successfully',
            'data' => $enrollments,
        ]);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        if ($event->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this event',
            ], 409);
        }

        if ($event->max_participants > 0) {
            $confirmed = $event->enrollments()->where('status', 'confirmed')->count();
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

        $enrollment = $event->enrollments()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in event',
            'data' => $enrollment,
        ], 201);
    }

    public function update(Request $request, Event $event, EventEnrollment $enrollment): JsonResponse
    {
        if ($enrollment->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled'])],
        ]);

        $enrollment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment status updated successfully',
            'data' => $enrollment,
        ]);
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $enrollment = $event->enrollments()->where('user_id', $request->user()->id)->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this event',
            ], 404);
        }

        $enrollment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unenrolled from event',
            'data' => null,
        ]);
    }

    public function myEnrollments(Request $request): JsonResponse
    {
        $enrollments = $request->user()->enrollments()->with('event')->get();

        return response()->json([
            'success' => true,
            'message' => 'Enrollments retrieved successfully',
            'data' => $enrollments,
        ]);
    }
}
