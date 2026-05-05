<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventLinkController extends Controller
{
    public function index(string $eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $links = $event->eventLinks;

        return response()->json([
            'success' => true,
            'message' => 'Event links ditemukan',
            'data' => $links
        ]);
    }

    public function store(Request $request, string $eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url'   => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $link = $event->eventLinks()->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Event link berhasil dibuat',
            'data' => $link
        ], 201);
    }

    public function update(Request $request, string $eventId, string $linkId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $link = $event->eventLinks()->find($linkId);

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'Event link not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'url'   => 'url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $link->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Event link berhasil diupdate',
            'data' => $link
        ]);
    }

    public function destroy(string $eventId, string $linkId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $link = $event->eventLinks()->find($linkId);

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'Event link not found'
            ], 404);
        }

        $link->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event link berhasil dihapus'
        ]);
    }
}
