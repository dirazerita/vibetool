<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $response = $next($request);

            if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\RedirectResponse) {
                $response->cookie('ref', $request->get('ref'), 60 * 24 * 30); // 30 days
            }

            return $response;
        }

        return $next($request);
    }
}
