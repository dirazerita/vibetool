<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Autentikasi untuk aplikasi Android native (folder "ANDROID NATIVE").
 *
 * Login mengembalikan Bearer token (plaintext sekali saja; DB menyimpan
 * SHA-256-nya). Registrasi memakai endpoint /api/auth/register yang sudah ada.
 */
class AppAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower($request->input('email')))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'ok' => false,
                'error' => 'invalid_credentials',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'ok' => false,
                'error' => 'account_pending',
                'message' => 'Akun kamu belum diaktifkan admin. Hubungi admin via WhatsApp untuk aktivasi.',
            ], 403);
        }

        $plainToken = Str::random(60);
        $user->forceFill(['api_token' => hash('sha256', $plainToken)])->save();

        return response()->json([
            'ok' => true,
            'token' => $plainToken,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->forceFill(['api_token' => null])->save();

        return response()->json(['ok' => true, 'message' => 'Berhasil keluar.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'user' => $this->formatUser($request->user())]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp_number' => $user->whatsapp_number,
            'referral_code' => $user->referral_code,
            'balance' => (float) $user->balance,
            'profile_photo' => $user->profile_photo ? asset('storage/'.$user->profile_photo) : null,
            'can_upload_product' => (bool) $user->can_upload_product,
        ];
    }
}
