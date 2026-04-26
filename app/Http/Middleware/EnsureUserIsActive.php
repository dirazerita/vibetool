<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isAdmin() && ($user->status ?? 'active') === 'pending') {
            session([
                'pending_user_data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'whatsapp_number' => $user->whatsapp_number,
                ],
            ]);

            return redirect()->route('pending')->with('warning', 'Akun Anda masih menunggu aktivasi oleh admin.');
        }

        return $next($request);
    }
}
