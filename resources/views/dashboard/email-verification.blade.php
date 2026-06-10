@extends('layouts.dashboard')
@section('title', 'Verifikasi Email')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Verifikasi Email</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="dk-card" style="padding:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-4">Status Email Anda</h2>

            <div class="mb-4">
                <div class="dk-text-muted" style="font-size:14px">Alamat Email</div>
                <div class="text-base font-medium" style="color:#e2e8f0; word-break:break-all;">{{ auth()->user()->email }}</div>
            </div>

            <div class="mb-4">
                <div class="dk-text-muted" style="font-size:14px">Status</div>
                @if($isVerified)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold" style="background:rgba(16,185,129,0.15); color:#6ee7b7;">
                        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Terverifikasi
                    </span>
                    @if($verifiedAt)
                        <p class="text-xs mt-2 dk-text-muted">Diverifikasi pada {{ $verifiedAt->format('d M Y H:i') }}</p>
                    @endif
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold" style="background:rgba(234,179,8,0.15); color:#fde047;">
                        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Belum Terverifikasi
                    </span>
                @endif
            </div>

            <div class="text-sm dk-text-muted" style="line-height:1.6;">
                <p class="mb-2"><strong style="color:#cbd5e1;">Kenapa perlu verifikasi?</strong></p>
                <ul style="list-style:disc; padding-left:18px;">
                    <li>Akun Anda <strong>tidak wajib</strong> diverifikasi untuk login.</li>
                    <li>Verifikasi <strong>wajib</strong> jika Anda mau menarik komisi.</li>
                    <li>Membantu memastikan email Anda valid untuk notifikasi.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        @if($isVerified)
            <div class="dk-card" style="padding:32px; text-align:center;">
                <div style="width:64px; height:64px; margin:0 auto 16px; border-radius:50%; background:rgba(16,185,129,0.15); display:flex; align-items:center; justify-content:center;">
                    <svg style="width:32px; height:32px; color:#6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-bold dk-heading mb-2">Email Anda Sudah Terverifikasi</h3>
                <p class="dk-text-muted mb-4">Anda sudah bisa melakukan penarikan komisi.</p>
                <a href="{{ route('dashboard.withdrawals') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    Halaman Penarikan
                </a>
            </div>
        @else
            <div class="dk-card" style="padding:24px;" x-data="{ cooldown: {{ (int) $cooldownSeconds }} }" x-init="if(cooldown > 0) { let t = setInterval(() => { cooldown--; if(cooldown <= 0) clearInterval(t); }, 1000); }">
                <h2 class="text-lg font-semibold dk-heading mb-4">Langkah Verifikasi</h2>

                <div style="background:rgba(59,130,246,0.1); border-left:3px solid #3b82f6; padding:12px 16px; border-radius:6px; margin-bottom:24px;">
                    <p class="text-sm" style="color:#93c5fd; margin:0;">
                        <strong>Cara verifikasi:</strong> Klik tombol "Kirim Kode Verifikasi" di bawah, lalu cek email <strong>{{ auth()->user()->email }}</strong>. Masukkan 6 digit kode yang dikirim ke kolom di bawah. Kode berlaku 15 menit.
                    </p>
                </div>

                <div class="mb-6">
                    <h3 class="text-sm font-semibold dk-heading mb-3">1. Minta Kode Verifikasi</h3>
                    <form method="POST" action="{{ route('dashboard.email-verification.send') }}">
                        @csrf
                        @if($codeSentAt)
                            <p class="text-xs dk-text-muted mb-3">
                                Kode terakhir dikirim {{ $codeSentAt->diffForHumans() }} ke <strong>{{ auth()->user()->email }}</strong>.
                                @if($codeExpiresAt && $codeExpiresAt->isFuture())
                                    Kedaluwarsa dalam {{ $codeExpiresAt->diffForHumans(['parts' => 2, 'short' => true]) }}.
                                @endif
                            </p>
                        @endif
                        <button type="submit" :disabled="cooldown > 0"
                                class="px-4 py-2 rounded-lg font-medium"
                                :class="cooldown > 0 ? 'bg-slate-600 text-slate-400 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                            <span x-show="cooldown <= 0" x-cloak>{{ $codeSentAt ? 'Kirim Ulang Kode' : 'Kirim Kode Verifikasi' }}</span>
                            <span x-show="cooldown > 0">Kirim ulang dalam <span x-text="cooldown"></span> detik</span>
                        </button>
                    </form>
                </div>

                <div class="mb-2">
                    <h3 class="text-sm font-semibold dk-heading mb-3">2. Masukkan Kode</h3>
                    <form method="POST" action="{{ route('dashboard.email-verification.verify') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="code" class="dk-label">Kode 6 Digit</label>
                            <input type="text" name="code" id="code" maxlength="6" minlength="6" pattern="[0-9]{6}" inputmode="numeric"
                                   class="w-full dk-input"
                                   style="font-size:24px; letter-spacing:8px; text-align:center; font-family:monospace;"
                                   placeholder="000000" required autocomplete="one-time-code">
                            @error('code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            Verifikasi Email
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
