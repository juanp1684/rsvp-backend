<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InviteeController;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Public event info
Route::get('/event', [EventController::class, 'show']);

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

    // Event image uploads
    Route::post('/event/images/{type}', [EventController::class, 'uploadImage']);

    // Invitee management
    Route::post('/invitees/import', [InviteeController::class, 'import']);
    Route::apiResource('invitees', InviteeController::class);
});
