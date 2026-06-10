<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\RealisticEmail;
use App\Services\TelegramService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class MemberRegisterController extends Controller
{
    /**
     * Endpoint JSON untuk registrasi member dari aplikasi software klien
     * (mis. Telegram Blaster). Mengembalikan akun baru berstatus `pending`
     * yang harus diaktifkan admin via WhatsApp sebelum bisa login.
     *
     * Request: { name, email, whatsapp_number, password, password_confirmation }
     * Response sukses (201): { ok: true, status: "pending", user: { id, name, email, whatsapp_number } }
     * Response gagal (422):  { ok: false, error: "validation_error", errors: { field: [messages] } }
     */
    public function store(Request $request): JsonResponse
    {
        $normalizedWhatsapp = PhoneNumber::normalize($request->input('whatsapp_number'));
        $request->merge(['whatsapp_number' => $normalizedWhatsapp]);

        $validator = validator(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new RealisticEmail],
                'whatsapp_number' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('users', 'whatsapp_number'),
                ],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor yang berbeda.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => 'validation_error',
                'message' => 'Data tidak valid. Periksa kembali isian.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'whatsapp_number' => $normalizedWhatsapp,
            'password' => Hash::make($request->input('password')),
            'status' => 'pending',
        ]);

        event(new Registered($user));

        try {
            app(TelegramService::class)->notifyNewMember($user->fresh()->load(['upline', 'intendedProduct']));
        } catch (\Throwable $e) {
            Log::warning('Telegram notify new member failed (API register)', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'status' => 'pending',
            'message' => 'Registrasi berhasil. Akun menunggu aktivasi admin via WhatsApp.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
            ],
        ], 201);
    }
}
