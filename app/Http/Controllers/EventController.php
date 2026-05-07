<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

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
            'civil_image_url'               => $event->civil_image               ? $disk->url($event->civil_image)               : null,
            'invitation_image_url'          => $event->invitation_image          ? $disk->url($event->invitation_image)          : null,
            'confirm_attending_image_url'  => $event->confirm_attending_image  ? $disk->url($event->confirm_attending_image)  : null,
            'confirm_declined_image_url'   => $event->confirm_declined_image   ? $disk->url($event->confirm_declined_image)   : null,
            'song_url'                     => $event->song                     ? $disk->url($event->song)                     : null,
            'dress_code_image_url'         => $event->dress_code_image         ? $disk->url($event->dress_code_image)         : null,
            'gift_suggestion_image_url'    => $event->gift_suggestion_image    ? $disk->url($event->gift_suggestion_image)    : null,
            'recommendations_image_url'   => $event->recommendations_image   ? $disk->url($event->recommendations_image)   : null,
            'carousel_images'             => $event->carouselImages->map(fn ($img) => [
                'id'  => $img->id,
                'url' => $disk->url($img->path),
            ])->values(),
        ]));
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'partner1_parent1'   => 'nullable|string|max:255',
            'partner1_parent2'   => 'nullable|string|max:255',
            'partner2_parent1'   => 'nullable|string|max:255',
            'partner2_parent2'   => 'nullable|string|max:255',
            'ceremony_at'        => 'required|date',
            'ceremony_location'  => 'required|string|max:255',
            'ceremony_url'       => 'nullable|url|max:255',
            'civil_at'           => 'nullable|date',
            'civil_location'     => 'nullable|string|max:255',
            'civil_url'          => 'nullable|url|max:255',
            'reception_at'       => 'required|date',
            'reception_location' => 'required|string|max:255',
            'reception_url'      => 'nullable|url|max:255',
            'dress_code'         => 'nullable|string|max:255',
            'rsvp_deadline'      => 'required|date|before:ceremony_at',
            'late_rsvp_deadline' => 'nullable|date|after_or_equal:rsvp_deadline|before:ceremony_at',
            'notes'              => 'nullable|string',
            'no_kids'                        => 'boolean',
            'no_kids_message'                => 'nullable|string|max:500',
            'confirm_attending_message'      => 'nullable|string|max:255',
            'confirm_declined_message'       => 'nullable|string|max:255',
            'civil_ceremony_same_venue'      => 'sometimes|boolean',
            'civil_reception_same_venue'     => 'sometimes|boolean',
            'ceremony_reception_same_venue'  => 'sometimes|boolean',
            'subdomain'                      => 'nullable|string|max:63',
            'subtitle'                       => 'nullable|string|max:500',
            'gift_suggestion'                => 'nullable|string',
            'recommendations'                => 'nullable|string',
        ]);

        if (isset($data['subdomain'])) {
            $data['subdomain'] = $this->normalizeSubdomain($data['subdomain']) ?: null;
        }

        $event->update($data);

        return response()->json($event->fresh());
    }

    public function uploadImage(Request $request, Event $event, string $type): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $allowed = ['couple', 'couple_mobile', 'ceremony', 'civil', 'reception', 'invitation', 'confirm_attending', 'confirm_declined', 'dress_code', 'gift_suggestion', 'recommendations'];

        if (! in_array($type, $allowed)) {
            return response()->json(['message' => 'Invalid image type.'], 422);
        }

        $column = $type . '_image';

        if ($event->$column) {
            Storage::disk('public')->delete($event->$column);
        }

        if ($type === 'invitation') {
            $encoded = (new ImageManager(new Driver()))
                ->decode($request->file('image'))
                ->scaleDown(width: 1200)
                ->encode(new JpegEncoder(80));

            $path = 'event/' . Str::random(40) . '.jpg';
            Storage::disk('public')->put($path, (string) $encoded);
        } else {
            $path = $request->file('image')->store('event', 'public');
        }

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
        $allowed = ['couple', 'couple_mobile', 'ceremony', 'civil', 'reception', 'invitation', 'confirm_attending', 'confirm_declined', 'dress_code', 'gift_suggestion', 'recommendations'];

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

    private function normalizeSubdomain(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = preg_replace('/[\s_]+/', '-', $value);
        $value = preg_replace('/[^a-z0-9-]/', '', $value);
        $value = preg_replace('/-+/', '-', $value);
        return trim($value, '-');
    }
}
