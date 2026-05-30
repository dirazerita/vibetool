<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class PublicSettingController extends Controller
{
    /**
     * Mengembalikan nomor WhatsApp admin (sudah dinormalisasi format 62xxxxxxxxxx)
     * untuk dipakai aplikasi software klien sebagai target tombol "Hubungi Admin".
     *
     * Nomor admin disimpan di tabel `settings` dengan key `whatsapp_admin`
     * dan diatur dari Admin Panel.
     */
    public function whatsappAdmin(): JsonResponse
    {
        $raw = Setting::get('whatsapp_admin');
        $number = PhoneNumber::normalize($raw);

        if (!$number) {
            return response()->json([
                'number' => null,
                'message' => 'Nomor WhatsApp admin belum dikonfigurasi.',
            ], 404);
        }

        return response()->json([
            'number' => $number,
        ]);
    }
}
