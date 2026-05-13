<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForbidViewers
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isViewer() && ! $request->isMethod('GET')) {
            return response()->json(['message' => 'Read-only access.'], 403);
        }

        return $next($request);
    }
}
