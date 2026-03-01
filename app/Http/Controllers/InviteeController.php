<?php

namespace App\Http\Controllers;

use App\Models\Invitee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class InviteeController extends Controller
{
    public function index(): JsonResponse
    {
        $invitees = Invitee::with('companions')->orderBy('full_name')->get();

        return response()->json($invitees);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'allowed_companions' => 'required|integer|min:0|max:10',
            'notes' => 'nullable|string',
        ]);

        $data['code'] = Str::upper(Str::random(8));

        $invitee = Invitee::create($data);

        return response()->json($invitee, 201);
    }

    public function show(Invitee $invitee): JsonResponse
    {
        return response()->json($invitee->load('companions'));
    }

    public function update(Request $request, Invitee $invitee): JsonResponse
    {
        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'allowed_companions' => 'sometimes|integer|min:0|max:10',
            'status' => 'sometimes|in:pending,attending,declined',
            'notes' => 'sometimes|nullable|string',
        ]);

        $invitee->update($data);

        return response()->json($invitee->load('companions'));
    }

    public function destroy(Invitee $invitee): JsonResponse
    {
        $invitee->delete();

        return response()->json(null, 204);
    }

    public function export(): JsonResponse
    {
        $rows = Invitee::with('companions')
            ->orderBy('full_name')
            ->get()
            ->flatMap(function (Invitee $invitee) {
                $primary = [
                    'type' => 'invitee',
                    'invitee_id' => $invitee->id,
                    'full_name' => $invitee->full_name,
                    'phone' => $invitee->phone,
                    'status' => $invitee->status,
                ];

                $companions = $invitee->companions->map(fn ($companion) => [
                    'type' => 'companion',
                    'invitee_id' => $invitee->id,
                    'full_name' => $companion->full_name,
                    'phone' => null,
                    'status' => $invitee->status,
                ]);

                return collect([$primary])->merge($companions);
            });

        return response()->json($rows);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        $path = $request->file('file')->store('imports');
        $fullPath = storage_path("app/private/{$path}");

        $imported = 0;

        SimpleExcelReader::create($fullPath)
            ->headersToSnakeCase()
            ->getRows()
            ->each(function (array $row) use (&$imported) {
                $fullName = trim($row['full_name'] ?? '');
                if (! $fullName) return;

                Invitee::create([
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
