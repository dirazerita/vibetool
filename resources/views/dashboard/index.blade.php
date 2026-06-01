@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Dashboard</h1>

<div class="dk-grid-4 gap-6 mb-8" style="display:grid;grid-template-columns:repeat(4,1fr)">
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(99,102,241,0.15);color:#818cf8;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Saldo</div>
            <div class="text-2xl font-bold" style="color:#818cf8">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(16,185,129,0.15);color:#6ee7b7;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Komisi</div>
            <div class="text-2xl font-bold" style="color:#6ee7b7">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(96,165,250,0.15);color:#60a5fa;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Penjualan</div>
            <div class="text-2xl font-bold" style="color:#60a5fa">{{ $totalOrders }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(168,85,247,0.15);color:#c4b5fd;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Downline</div>
            <div class="text-2xl font-bold" style="color:#c4b5fd">{{ $totalDownlines }}</div>
        </div>
    </div>
</div>

<div class="dk-card" style="padding:24px;">
    <h2 class="text-lg font-semibold dk-heading mb-4">Kode Referral Anda</h2>
    <div class="flex items-center gap-4">
        <div class="px-6 py-3 rounded-lg font-mono text-lg font-bold" style="background:#151e2d;color:#818cf8;border:1px solid #2d3a4a">{{ $user->referral_code }}</div>
        <div class="dk-text-muted" style="font-size:14px">
            <p>Link referral registrasi:</p>
            <code class="text-xs px-2 py-1 rounded" style="background:#151e2d;color:#a5b4fc;border:1px solid #2d3a4a">{{ url('/register?ref=' . $user->referral_code) }}</code>
        </div>
    </div>
</div>

@if(!empty($welcomeModal))
<div id="welcomeModalBackdrop" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md rounded-2xl shadow-2xl" style="background:#1a2332;border:1px solid #2d3a4a overflow-hidden animate-fade-in">
        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 px-6 py-5 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-widest text-indigo-100">Selamat datang, {{ $user->name }}!</p>
                    <h3 class="mt-1 text-xl font-bold">
                        @if($welcomeModal['type'] === 'pending_order')
                            Pesanan Anda Menunggu Pembayaran
                        @else
                            Selesaikan Pembelian Anda
                        @endif
                    </h3>
                </div>
                <button type="button" onclick="document.getElementById('welcomeModalBackdrop').remove()" class="text-white/80 hover:text-white text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <p class="dk-text" style="font-size:14px">
                @if($welcomeModal['type'] === 'pending_order')
                    Akun Anda sudah aktif. Berikut produk yang sebelumnya Anda pesan — lanjutkan pembayarannya untuk mengakses produk.
                @else
                    Akun Anda sudah aktif. Berikut produk yang sebelumnya ingin Anda beli — klik tombol di bawah untuk menyelesaikan pembelian.
                @endif
            </p>

            <div class="flex gap-4 p-4 rounded-xl" style="background:#151e2d;border:1px solid #2d3a4a">
                @php
                    $thumb = $welcomeModal['product']->thumbnail ?? null;
                @endphp
                @if($thumb)
                    <img src="{{ asset('storage/' . $thumb) }}" alt="{{ $welcomeModal['product']->title }}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                @else
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex-shrink-0"></div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold dk-heading line-clamp-2">{{ $welcomeModal['product']->title }}</p>
                    <p class="mt-1 text-base font-bold" style="color:#818cf8">Rp {{ number_format($welcomeModal['product']->price, 0, ',', '.') }}</p>
                    @if($welcomeModal['type'] === 'pending_order')
                        <p class="mt-1 dk-text-muted" style="font-size:12px">Order #{{ $welcomeModal['order']->id }} &middot; {{ $welcomeModal['order']->created_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($welcomeModal['type'] === 'pending_order' && $welcomeModal['order']->payment_proof)
                <div class="text-xs rounded-lg p-3" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);color:#fbbf24">
                    Bukti pembayaran sudah diupload. Pesanan sedang menunggu konfirmasi admin.
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ $welcomeModal['cta_url'] }}" class="flex-1 inline-flex items-center justify-center dk-btn dk-btn-primary text-sm font-semibold px-4 py-3 rounded-lg">
                    {{ $welcomeModal['cta_label'] }}
                </a>
                <button type="button" onclick="document.getElementById('welcomeModalBackdrop').remove()" class="flex-1 inline-flex items-center justify-center dk-btn dk-btn-outline text-sm font-medium px-4 py-3 rounded-lg">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.25s ease-out; }
</style>
@endif
@endsection
