<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCarouselImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventCarouselImageController extends Controller
{
    public function store(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($event->carouselImages()->count() >= 3) {
            return response()->json(['message' => 'Maximum of 3 carousel images allowed.'], 422);
        }

        $path = $request->file('image')->store('event/carousel', 'public');

        $nextOrder = ($event->carouselImages()->max('sort_order') ?? -1) + 1;

        $image = $event->carouselImages()->create([
            'path'       => $path,
            'sort_order' => $nextOrder,
        ]);

        return response()->json([
            'id'  => $image->id,
            'url' => Storage::disk('public')->url($path),
        ], 201);
    }

    public function reorder(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($request->ids as $order => $id) {
            $event->carouselImages()->where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(null, 204);
    }

    public function destroy(Event $event, EventCarouselImage $carouselImage): JsonResponse
    {
        abort_if($carouselImage->event_id !== $event->id, 404);

        Storage::disk('public')->delete($carouselImage->path);
        $carouselImage->delete();

        return response()->json(null, 204);
    }
}
