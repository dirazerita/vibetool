<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi Bearer token untuk API aplikasi Android native.
 * Token disimpan di kolom users.api_token (di-hash SHA-256).
 */
class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['ok' => false, 'error' => 'unauthenticated', 'message' => 'Token tidak ditemukan.'], 401);
        }

        $user = User::where('api_token', hash('sha256', $token))->first();

        if (! $user) {
            return response()->json(['ok' => false, 'error' => 'unauthenticated', 'message' => 'Token tidak valid.'], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json(['ok' => false, 'error' => 'account_inactive', 'message' => 'Akun belum aktif. Hubungi admin.'], 403);
        }

        // Set user ke guard supaya $request->user() & auth() bekerja normal.
        Auth::setUser($user);

        return $next($request);
    }
}
