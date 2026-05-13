<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        return response()->json(
            User::with('event:id,name,slug')
                ->orderBy('name')
                ->get()
                ->map(fn ($u) => [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'role'       => $u->role,
                    'event_id'   => $u->event_id,
                    'event_name' => $u->event?->name,
                    'event_slug' => $u->event?->slug,
                ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,viewer,super_admin',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'event_id' => in_array($data['role'], ['admin', 'viewer']) ? ($data['event_id'] ?? null) : null,
        ]);

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'event_id'   => $user->event_id,
            'event_name' => $user->event?->name,
            'event_slug' => $user->event?->slug,
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,viewer,super_admin',
            'event_id' => 'nullable|exists:events,id',
            'password' => 'nullable|string|min:8',
        ]);

        // Prevent super admin from demoting themselves
        if ($request->user()->id === $user->id && $data['role'] !== 'super_admin') {
            return response()->json(['message' => 'You cannot change your own role.'], 422);
        }

        $update = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'event_id' => in_array($data['role'], ['admin', 'viewer']) ? ($data['event_id'] ?? null) : null,
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        $user->load('event:id,name,slug');

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'event_id'   => $user->event_id,
            'event_name' => $user->event?->name,
            'event_slug' => $user->event?->slug,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
