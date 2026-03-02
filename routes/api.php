<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InviteeController;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Public event info (by slug)
Route::get('/event/{slug}', [EventController::class, 'show']);

// Public RSVP routes (by event slug + invitee code)
Route::get('/rsvp/{eventSlug}/{code}', [RsvpController::class, 'show']);
Route::post('/rsvp/{eventSlug}/{code}', [RsvpController::class, 'submit']);

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Event list (super admin only — controller enforces)
    Route::get('/events', [EventController::class, 'index']);

    // All event-scoped routes — active.event middleware checks ownership
    Route::middleware('active.event')->prefix('events/{event}')->group(function () {
        Route::post('/images/{type}', [EventController::class, 'uploadImage']);
        Route::post('/invitees/import', [InviteeController::class, 'import']);
        Route::apiResource('invitees', InviteeController::class);
    });
});
