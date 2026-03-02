<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function show(): JsonResponse
    {
        $event = Event::first();
        if (! $event) return response()->json(null);

        return response()->json(array_merge($event->toArray(), [
            'deadline_passed'      => now()->isAfter($event->rsvp_deadline),
            'couple_image_url'     => $event->couple_image     ? Storage::url($event->couple_image)     : null,
            'ceremony_image_url'   => $event->ceremony_image   ? Storage::url($event->ceremony_image)   : null,
            'reception_image_url'  => $event->reception_image  ? Storage::url($event->reception_image)  : null,
            'invitation_image_url' => $event->invitation_image ? Storage::url($event->invitation_image) : null,
        ]));
    }

    public function uploadImage(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if (! in_array($type, ['couple', 'ceremony', 'reception', 'invitation'])) {
            return response()->json(['message' => 'Invalid image type.'], 422);
        }

        $event = Event::firstOrFail();
        $column = $type . '_image';

        if ($event->$column) {
            Storage::disk('public')->delete($event->$column);
        }

        $path = $request->file('image')->store('event', 'public');
        $event->update([$column => $path]);

        return response()->json(['url' => Storage::url($path)]);
    }
}
