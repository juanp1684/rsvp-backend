<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invitee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class InviteeController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        return response()->json(
            Invitee::where('event_id', $event->id)
                ->with('companions')
                ->orderBy('full_name')
                ->get()
        );
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'allowed_companions' => 'required|integer|min:0|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $data['code'] = Str::upper(Str::random(8));
        $data['event_id'] = $event->id;

        return response()->json(Invitee::create($data), 201);
    }

    public function show(Event $event, Invitee $invitee): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);

        return response()->json($invitee->load('companions'));
    }

    public function update(Request $request, Event $event, Invitee $invitee): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);

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

    public function destroy(Event $event, Invitee $invitee): JsonResponse
    {
        abort_if($invitee->event_id !== $event->id, 404);

        $invitee->delete();

        return response()->json(null, 204);
    }

    public function bulkDestroy(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'uuid',
        ]);

        Invitee::where('event_id', $event->id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json(null, 204);
    }

    public function import(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $path = $request->file('file')->store('imports');
        $fullPath = storage_path("app/private/{$path}");

        $imported = 0;

        $rows = [];
        $errors = [];
        $rowNumber = 1;

        SimpleExcelReader::create($fullPath)
            ->getRows()
            ->each(function (array $row) use (&$rows, &$errors, &$rowNumber) {
                $rowNumber++;

                $normalized = collect($row)->mapWithKeys(fn ($v, $k) => [
                    mb_strtolower(trim($k)) => $v,
                ])->all();

                $nombre = trim($normalized['nombre'] ?? '');
                $companions = $normalized['acompañantes'] ?? $normalized['acompanantes'] ?? null;

                if ($nombre === '') {
                    $errors[] = ['row' => $rowNumber, 'field' => 'nombre', 'message' => 'El nombre es requerido.'];
                }

                if ($companions !== null && $companions !== '') {
                    if (! preg_match('/^\d+$/', (string) $companions)) {
                        $errors[] = ['row' => $rowNumber, 'field' => 'acompañantes', 'message' => 'Debe ser un número entero (ej. 0, 1, 2).'];
                    } elseif ((int) $companions < 0 || (int) $companions > 20) {
                        $errors[] = ['row' => $rowNumber, 'field' => 'acompañantes', 'message' => 'Debe ser un número entre 0 y 20.'];
                    }
                }

                $rows[] = ['normalized' => $normalized, 'nombre' => $nombre];
            });

        if (! empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        foreach ($rows as $item) {
            $normalized = $item['normalized'];
            $fullName = $item['nombre'];
            if (! $fullName) continue;

            $companions = $normalized['acompañantes'] ?? $normalized['acompanantes'] ?? null;

            Invitee::create([
                'event_id' => $event->id,
                'full_name' => $fullName,
                'phone' => $normalized['teléfono'] ?? $normalized['telefono'] ?? null,
                'allowed_companions' => ($companions !== null && $companions !== '') ? (int) $companions : 0,
                'notes' => $normalized['notas'] ?? null,
                'code' => Str::upper(Str::random(8)),
            ]);
            $imported++;
        }

        return response()->json(['imported' => $imported]);
    }
}
