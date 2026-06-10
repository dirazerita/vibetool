<?php

namespace App\Rules;

use App\Support\DisposableEmailDomains;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RealisticEmail implements ValidationRule
{
    /**
     * Validasi tambahan untuk email saat registrasi:
     * - Tolak domain yang masuk daftar disposable mail.
     * - Tolak domain yang tidak punya MX record (atau A record sebagai fallback).
     *
     * Tujuannya meminimalisasi pendaftaran dengan email palsu / domain
     * tidak ada / temp-mail. Tidak menjamin email mailbox-nya benar-benar
     * eksis (untuk itu butuh OTP).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $email = strtolower(trim($value));
        $atPos = strrpos($email, '@');
        if ($atPos === false) {
            return;
        }
        $domain = substr($email, $atPos + 1);
        if ($domain === '') {
            return;
        }

        if (DisposableEmailDomains::isDisposable($email)) {
            $fail('Email dari layanan disposable / sekali pakai tidak diperbolehkan. Gunakan email pribadi yang aktif.');

            return;
        }

        if (! $this->domainHasMxOrA($domain)) {
            $fail('Domain email ini tidak terdaftar (tidak ditemukan MX record). Pastikan email yang Anda masukkan benar.');

            return;
        }
    }

    private function domainHasMxOrA(string $domain): bool
    {
        // Skip cek DNS saat testing supaya unit test deterministik.
        if (app()->environment('testing') && config('mail.skip_dns_check', true)) {
            return true;
        }

        try {
            if (function_exists('checkdnsrr') && @checkdnsrr($domain, 'MX')) {
                return true;
            }
            if (function_exists('checkdnsrr') && @checkdnsrr($domain, 'A')) {
                return true;
            }
        } catch (\Throwable $e) {
            // DNS sedang bermasalah → biarkan email lolos supaya tidak misleading.
            return true;
        }

        return false;
    }
}
