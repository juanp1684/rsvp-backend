<?php

use App\Http\Controllers\OgController;
use Illuminate\Support\Facades\Route;

Route::get('/rsvp/{eventSlug}/{code}', [OgController::class, 'rsvp']);
