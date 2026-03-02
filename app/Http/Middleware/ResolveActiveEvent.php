<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveEvent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $eventId = $request->header('X-Event-Id');
            abort_if(! $eventId, 400, 'X-Event-Id header required for super admins.');
            $event = Event::findOrFail($eventId);
        } else {
            $event = $user->event;
            abort_if(! $event, 403, 'No event assigned to your account.');
        }

        $request->attributes->set('active_event', $event);

        return $next($request);
    }
}
