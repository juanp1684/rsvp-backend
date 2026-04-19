<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        return response()->json(Event::orderBy('name')->get());
    }

    public function show(string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $disk = Storage::disk('public');

        return response()->json(array_merge($event->toArray(), [
            'deadline_passed'             => now()->isAfter($event->rsvp_deadline),
            'couple_image_url'            => $event->couple_image        ? $disk->url($event->couple_image)        : null,
            'couple_mobile_image_url'     => $event->couple_mobile_image ? $disk->url($event->couple_mobile_image) : null,
            'ceremony_image_url'          => $event->ceremony_image      ? $disk->url($event->ceremony_image)      : null,
            'reception_image_url'         => $event->reception_image     ? $disk->url($event->reception_image)     : null,
            'invitation_image_url'        => $event->invitation_image    ? $disk->url($event->invitation_image)    : null,
        ]));
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'ceremony_at'        => 'required|date',
            'reception_at'       => 'required|date',
            'ceremony_location'  => 'required|string|max:255',
            'ceremony_url'       => 'nullable|url|max:255',
            'reception_location' => 'required|string|max:255',
            'reception_url'      => 'nullable|url|max:255',
            'dress_code'         => 'nullable|string|max:255',
            'rsvp_deadline'      => 'required|date|before:ceremony_at',
            'late_rsvp_deadline' => 'nullable|date|after_or_equal:rsvp_deadline|before:ceremony_at',
            'notes'              => 'nullable|string',
        ]);

        $event->update($data);

        return response()->json($event->fresh());
    }

    public function uploadImage(Request $request, Event $event, string $type): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if (! in_array($type, ['couple', 'couple_mobile', 'ceremony', 'reception', 'invitation'])) {
            return response()->json(['message' => 'Invalid image type.'], 422);
        }

        $column = $type . '_image';

        if ($event->$column) {
            Storage::disk('public')->delete($event->$column);
        }

        $path = $request->file('image')->store('event', 'public');
        $event->update([$column => $path]);

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    public function destroyImage(Event $event, string $type): JsonResponse
    {
        if (! in_array($type, ['couple', 'couple_mobile', 'ceremony', 'reception', 'invitation'])) {
            return response()->json(['message' => 'Invalid image type.'], 422);
        }

        $column = $type . '_image';

        if ($event->$column) {
            Storage::disk('public')->delete($event->$column);
            $event->update([$column => null]);
        }

        return response()->json(null, 204);
    }
}
