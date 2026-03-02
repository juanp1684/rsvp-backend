<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveEvent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $event = $request->route('event');

        abort_unless($user->isSuperAdmin() || $user->event_id === $event->id, 403);

        return $next($request);
    }
}
