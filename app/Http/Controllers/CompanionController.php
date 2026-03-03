<?php

namespace App\Http\Controllers;

use App\Models\Companion;
use App\Models\Event;
use App\Models\Invitee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanionController extends Controller
{
    public function store(Request $request, Event $event, Invitee $invitee): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);
        abort_if(
            $invitee->companions()->count() >= $invitee->allowed_companions,
            422,
            'Companion limit reached.'
        );

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
        ]);

        $companion = $invitee->companions()->create($data);

        return response()->json($companion, 201);
    }

    public function update(Request $request, Event $event, Invitee $invitee, Companion $companion): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);
        abort_if($companion->invitee_id !== $invitee->id, 404);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
        ]);

        $companion->update($data);

        return response()->json($companion);
    }

    public function destroy(Event $event, Invitee $invitee, Companion $companion): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);
        abort_if($companion->invitee_id !== $invitee->id, 404);

        $companion->delete();

        return response()->json(null, 204);
    }
}
