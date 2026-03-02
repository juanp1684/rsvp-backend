<?php

namespace App\Http\Controllers;

use App\Models\Invitee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class InviteeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $event = $request->attributes->get('active_event');

        $invitees = Invitee::where('event_id', $event->id)
            ->with('companions')
            ->orderBy('full_name')
            ->get();

        return response()->json($invitees);
    }

    public function store(Request $request): JsonResponse
    {
        $event = $request->attributes->get('active_event');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'allowed_companions' => 'required|integer|min:0|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $data['code'] = Str::upper(Str::random(8));
        $data['event_id'] = $event->id;

        $invitee = Invitee::create($data);

        return response()->json($invitee, 201);
    }

    public function show(Request $request, Invitee $invitee): JsonResponse
    {
        $event = $request->attributes->get('active_event');
        abort_if($invitee->event_id !== $event->id, 403);

        return response()->json($invitee->load('companions'));
    }

    public function update(Request $request, Invitee $invitee): JsonResponse
    {
        $event = $request->attributes->get('active_event');
        abort_if($invitee->event_id !== $event->id, 403);

        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'allowed_companions' => 'sometimes|integer|min:0|max:10',
            'status' => 'sometimes|in:pending,attending,declined',
            'notes' => 'sometimes|nullable|string|max:500',
        ]);

        $invitee->update($data);

        return response()->json($invitee->load('companions'));
    }

    public function destroy(Request $request, Invitee $invitee): JsonResponse
    {
        $event = $request->attributes->get('active_event');
        abort_if($invitee->event_id !== $event->id, 403);

        $invitee->delete();

        return response()->json(null, 204);
    }

    public function import(Request $request): JsonResponse
    {
        $event = $request->attributes->get('active_event');

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $path = $request->file('file')->store('imports');
        $fullPath = storage_path("app/private/{$path}");

        $imported = 0;

        SimpleExcelReader::create($fullPath)
            ->headersToSnakeCase()
            ->getRows()
            ->each(function (array $row) use ($event, &$imported) {
                $fullName = trim($row['full_name'] ?? '');
                if (! $fullName) return;

                Invitee::create([
                    'event_id' => $event->id,
                    'full_name' => $fullName,
                    'phone' => $row['phone'] ?? null,
                    'allowed_companions' => (int) ($row['allowed_companions'] ?? 0),
                    'notes' => $row['notes'] ?? null,
                    'code' => Str::upper(Str::random(8)),
                ]);

                $imported++;
            });

        return response()->json(['imported' => $imported]);
    }
}
