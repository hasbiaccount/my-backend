<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            'data'    => $events,
        ]);
    }

    public function myOrganized(Request $request): JsonResponse
    {
        $events = $request->user()->events()->with(['category:id,name,slug', 'images'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Organized events retrieved successfully',
            'data'    => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'category_id'           => 'nullable|exists:categories,id',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'location'              => 'required|string|max:255',
            'max_participants'      => 'nullable|integer|min:1',
            'registration_open'     => 'nullable|date',
            'registration_deadline' => 'nullable|date|before_or_equal:start_date',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        // Always free — ensure no fee stored
        $validated['registration_fee'] = 0;

        $event = Event::create([
            'organizer_id' => $request->user()->id,
            ...$validated,
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $this->storeImage($event, $request->file('image'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'data'    => $event->load('images'),
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
            'data'    => $event->load(['organizer:id,name', 'category:id,name,slug', 'eventLinks', 'images']),
        ]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'                 => 'sometimes|string|max:255',
            'description'           => 'sometimes|string',
            'category_id'           => 'sometimes|nullable|exists:categories,id',
            'start_date'            => 'sometimes|date',
            'end_date'              => 'sometimes|date|after_or_equal:start_date',
            'location'              => 'sometimes|string|max:255',
            'max_participants'      => 'sometimes|integer|min:1',
            'registration_open'     => 'sometimes|nullable|date',
            'registration_deadline' => 'sometimes|nullable|date|before_or_equal:start_date',
            'image'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        // Always free
        $validated['registration_fee'] = 0;

        $event->update($validated);

        // Handle image replacement
        if ($request->hasFile('image')) {
            // Delete old images
            foreach ($event->images as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
            $this->storeImage($event, $request->file('image'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully',
            'data'    => $event->load('images'),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        // Delete associated images from storage
        foreach ($event->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

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
            'data'    => $event->images()->get(),
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function storeImage(Event $event, $file): void
    {
        $ext      = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $ext;
        $path     = $file->storeAs('events', $filename, 'public');

        Image::create([
            'event_id' => $event->id,
            'path'     => $path,
        ]);
    }
}
