<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.settings', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'referral_code' => strtoupper(trim((string) $request->input('referral_code'))),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'referral_code' => [
                'required',
                'string',
                'min:4',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('users', 'referral_code')->ignore($user->id),
            ],
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
        ], [
            'referral_code.regex' => 'Kode referral hanya boleh berisi huruf (A-Z) dan angka (0-9).',
            'referral_code.unique' => 'Kode referral ini sudah dipakai member lain. Pilih kode lain.',
            'referral_code.min' => 'Kode referral minimal 4 karakter.',
            'referral_code.max' => 'Kode referral maksimal 20 karakter.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'referral_code' => $validated['referral_code'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
