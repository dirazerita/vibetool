<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public const CODE_TTL_MINUTES = 15;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    public function show(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.email-verification', [
            'isVerified' => $user->email_verified_at !== null,
            'verifiedAt' => $user->email_verified_at,
            'codeSentAt' => $user->email_verification_last_sent_at,
            'codeExpiresAt' => $user->email_verification_expires_at,
            'cooldownSeconds' => $this->cooldownRemaining($user),
        ]);
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard.email-verification')
                ->with('success', 'Email Anda sudah terverifikasi.');
        }

        $cooldown = $this->cooldownRemaining($user);
        if ($cooldown > 0) {
            return redirect()->route('dashboard.email-verification')
                ->with('error', "Mohon tunggu {$cooldown} detik sebelum minta kode lagi.");
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_verification_code_hash' => Hash::make($code),
            'email_verification_expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'email_verification_attempts' => 0,
            'email_verification_last_sent_at' => now(),
        ])->save();

        try {
            Mail::to($user->email)->send(new EmailVerificationCodeMail($user, $code));
        } catch (\Throwable $e) {
            Log::warning('Email verification: send code failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard.email-verification')
                ->with('error', 'Gagal mengirim email. Cek alamat email Anda atau coba lagi nanti.');
        }

        return redirect()->route('dashboard.email-verification')
            ->with('success', 'Kode verifikasi sudah dikirim ke '.$this->maskEmail($user->email).'. Cek inbox / spam folder, kode berlaku '.self::CODE_TTL_MINUTES.' menit.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
            'code.size' => 'Kode verifikasi harus 6 digit.',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard.email-verification')
                ->with('success', 'Email Anda sudah terverifikasi.');
        }

        if (! $user->email_verification_code_hash || ! $user->email_verification_expires_at) {
            return back()->with('error', 'Kode belum diminta. Klik "Kirim Kode Verifikasi" terlebih dulu.');
        }

        if (now()->greaterThan($user->email_verification_expires_at)) {
            return back()->with('error', 'Kode sudah kedaluwarsa. Klik "Kirim Ulang Kode" untuk minta kode baru.');
        }

        if ($user->email_verification_attempts >= self::MAX_ATTEMPTS) {
            return back()->with('error', 'Terlalu banyak percobaan. Klik "Kirim Ulang Kode" untuk minta kode baru.');
        }

        $code = trim((string) $request->input('code'));

        if (! Hash::check($code, $user->email_verification_code_hash)) {
            $user->increment('email_verification_attempts');

            return back()->with('error', 'Kode tidak cocok. Sisa percobaan: '.max(0, self::MAX_ATTEMPTS - $user->email_verification_attempts).'.');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
            'email_verification_attempts' => 0,
        ])->save();

        return redirect()->route('dashboard.email-verification')
            ->with('success', 'Email berhasil diverifikasi. Sekarang Anda bisa menarik komisi.');
    }

    private function cooldownRemaining($user): int
    {
        if (! $user->email_verification_last_sent_at) {
            return 0;
        }

        // Carbon 3 mengembalikan diffInSeconds() bertanda (signed): karena
        // last_sent_at di masa lalu, now()->diffInSeconds($last) bernilai
        // NEGATIF, sehingga cooldown malah membesar dan tombol "Kirim Kode"
        // tidak pernah aktif lagi. Hitung elapsed dari selisih timestamp
        // supaya konsisten di Carbon 2 maupun 3.
        $elapsed = now()->getTimestamp() - $user->email_verification_last_sent_at->getTimestamp();
        $diff = self::RESEND_COOLDOWN_SECONDS - $elapsed;

        return max(0, (int) $diff);
    }

    private function maskEmail(string $email): string
    {
        $atPos = strrpos($email, '@');
        if ($atPos === false || $atPos < 2) {
            return $email;
        }
        $local = substr($email, 0, $atPos);
        $domain = substr($email, $atPos);
        $visible = Str::substr($local, 0, 2);

        return $visible.str_repeat('*', max(1, strlen($local) - 2)).$domain;
    }
}
