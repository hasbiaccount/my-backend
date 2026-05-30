<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show(string $id) {
        $image = Image::where('id', $id)->first();
        
        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        $path = storage_path('app/public/' . $image->path);
        
        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Image file not found'
            ], 404);
        }
        
        return response()->download($path);
    }

    public function upload(Request $request, string $eventId)
    {
        Event::findOrFail($eventId);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');

            $image = Image::create([
                'path' => $path,
                'event_id' => $eventId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $image
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Upload failed'
        ], 400);
    }

    public function destroy(string $id)
    {
        $image = Image::where('id', $id)->first();

        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        Storage::disk('public')->delete($image->path);

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ], 200);
    }
}
