<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InviteeController;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Public event info
Route::get('/event', function () {
    $event = \App\Models\Event::first();
    if (! $event) return response()->json(null);
    return response()->json(array_merge($event->toArray(), [
        'deadline_passed' => now()->isAfter($event->rsvp_deadline),
    ]));
});

// Public RSVP routes
Route::prefix('rsvp')->group(function () {
    Route::get('/{code}', [RsvpController::class, 'show']);
    Route::post('/{code}', [RsvpController::class, 'submit']);
});

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Invitee management
    Route::post('/invitees/import', [InviteeController::class, 'import']);
    Route::apiResource('invitees', InviteeController::class);
});
