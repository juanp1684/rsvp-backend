<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invitee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function show(string $eventSlug, string $code): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();
        $invitee = Invitee::where('code', $code)
            ->where('event_id', $event->id)
            ->firstOrFail();

        return response()->json($invitee->load('companions'));
    }

    public function submit(Request $request, string $eventSlug, string $code): JsonResponse
    {
        $event = Event::where('slug', $eventSlug)->firstOrFail();

        if ($event->rsvp_deadline && now()->isAfter($event->rsvp_deadline)) {
            return response()->json(['message' => 'RSVP deadline has passed.'], 403);
        }

        $invitee = Invitee::where('code', $code)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:attending,declined',
            'notes' => 'nullable|string|max:500',
            'companions' => 'nullable|array|max:' . $invitee->allowed_companions,
            'companions.*.full_name' => 'required|string|max:255',
        ]);

        $invitee->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $invitee->notes,
        ]);

        if ($data['status'] === 'attending' && ! empty($data['companions'])) {
            $invitee->companions()->delete();
            $invitee->companions()->createMany($data['companions']);
        } elseif ($data['status'] === 'declined') {
            $invitee->companions()->delete();
        }

        return response()->json($invitee->load('companions'));
    }
}
