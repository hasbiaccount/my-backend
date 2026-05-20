<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with(['organizer:id,name', 'category:id,name,slug', 'images'])
            ->withCount(['participants' => function ($q) {
                $q->whereIn('status', ['registered', 'attended']);
            }]);

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        } elseif ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $events = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Events retrieved successfully',
            'data' => $events,
        ]);
    }

    public function myOrganized(Request $request): JsonResponse
    {
        $events = $request->user()->events()->with(['category:id,name,slug', 'images'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Organized events retrieved successfully',
            'data' => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'registration_fee' => 'nullable|numeric|min:0',
            'registration_open' => 'nullable|date|after_or_equal:today',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $event = Event::create([
            'organizer_id' => $request->user()->id,
            ...$validator->validated(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data' => $event,
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event->loadCount(['participants' => function ($q) {
            $q->whereIn('status', ['registered', 'attended']);
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Event retrieved successfully',
            'data' => $event->load(['organizer:id,name', 'category:id,name,slug', 'eventLinks', 'images']),
        ]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'location' => 'sometimes|string|max:255',
            'max_participants' => 'sometimes|integer|min:1',
            'registration_fee' => 'sometimes|numeric|min:0',
            'registration_open' => 'sometimes|nullable|date',
            'registration_deadline' => 'sometimes|nullable|date|before_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $event->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data' => $event,
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }

    public function getImages(Event $event): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Event images retrieved successfully',
            'data' => $event->images()->get(),
        ]);
    }
}
