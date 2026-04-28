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
            'invitation_image_url'          => $event->invitation_image          ? $disk->url($event->invitation_image)          : null,
            'confirm_attending_image_url'  => $event->confirm_attending_image  ? $disk->url($event->confirm_attending_image)  : null,
            'confirm_declined_image_url'   => $event->confirm_declined_image   ? $disk->url($event->confirm_declined_image)   : null,
            'song_url'                     => $event->song                     ? $disk->url($event->song)                     : null,
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
            'no_kids'                    => 'boolean',
            'no_kids_message'            => 'nullable|string|max:500',
            'confirm_attending_message'  => 'nullable|string|max:255',
            'confirm_declined_message'   => 'nullable|string|max:255',
        ]);

        $event->update($data);

        return response()->json($event->fresh());
    }

    public function uploadImage(Request $request, Event $event, string $type): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $allowed = ['couple', 'couple_mobile', 'ceremony', 'reception', 'invitation', 'confirm_attending', 'confirm_declined'];

        if (! in_array($type, $allowed)) {
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

    public function uploadSong(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'song' => 'required|mimes:mp3|max:10240',
        ]);

        if ($event->song) {
            Storage::disk('public')->delete($event->song);
        }

        $original  = pathinfo($request->file('song')->getClientOriginalName(), PATHINFO_FILENAME);
        $slug      = preg_replace('/[^a-z0-9]+/', '-', strtolower($original));
        $slug      = trim($slug, '-');
        $suffix    = substr(bin2hex(random_bytes(3)), 0, 6);
        $filename  = "{$slug}-{$suffix}.mp3";

        $path = $request->file('song')->storeAs('event/audio', $filename, 'public');
        $event->update(['song' => $path]);

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    public function destroySong(Event $event): JsonResponse
    {
        if ($event->song) {
            Storage::disk('public')->delete($event->song);
            $event->update(['song' => null]);
        }

        return response()->json(null, 204);
    }

    public function destroyImage(Event $event, string $type): JsonResponse
    {
        $allowed = ['couple', 'couple_mobile', 'ceremony', 'reception', 'invitation', 'confirm_attending', 'confirm_declined'];

        if (! in_array($type, $allowed)) {
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
