<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ReferralCodeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'social_instagram' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_website' => 'nullable|url|max:255',
        ], [
            'referral_code.regex' => 'Kode referral hanya boleh berisi huruf (A-Z) dan angka (0-9).',
            'referral_code.unique' => 'Kode referral ini sudah dipakai member lain. Pilih kode lain.',
            'referral_code.min' => 'Kode referral minimal 4 karakter.',
            'referral_code.max' => 'Kode referral maksimal 20 karakter.',
            'profile_photo.image' => 'File harus berupa gambar.',
            'profile_photo.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'profile_photo.max' => 'Ukuran foto maksimal 2MB.',
            'social_website.url' => 'Format URL website tidak valid.',
        ]);

        $oldCode = $user->referral_code;
        $newCode = $validated['referral_code'];

        $updateData = [
            'name' => $validated['name'],
            'referral_code' => $newCode,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'social_instagram' => $validated['social_instagram'] ?? null,
            'social_facebook' => $validated['social_facebook'] ?? null,
            'social_twitter' => $validated['social_twitter'] ?? null,
            'social_tiktok' => $validated['social_tiktok'] ?? null,
            'social_youtube' => $validated['social_youtube'] ?? null,
            'social_website' => $validated['social_website'] ?? null,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $updateData['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if ($request->boolean('remove_photo') && $user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $updateData['profile_photo'] = null;
        }

        $user->update($updateData);

        if ($oldCode !== $newCode) {
            ReferralCodeHistory::create([
                'user_id' => $user->id,
                'old_code' => $oldCode,
                'new_code' => $newCode,
                'changed_by_id' => $user->id,
                'changed_by_role' => ReferralCodeHistory::ROLE_SELF,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
