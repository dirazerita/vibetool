@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Saldo</div>
        <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Komisi</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Penjualan</div>
        <div class="text-2xl font-bold text-blue-600">{{ $totalOrders }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Downline</div>
        <div class="text-2xl font-bold text-purple-600">{{ $totalDownlines }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kode Referral Anda</h2>
    <div class="flex items-center gap-4">
        <div class="bg-gray-100 px-6 py-3 rounded-lg font-mono text-lg font-bold text-indigo-600">{{ $user->referral_code }}</div>
        <div class="text-sm text-gray-500">
            <p>Link referral registrasi:</p>
            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ url('/register?ref=' . $user->referral_code) }}</code>
        </div>
    </div>
</div>

@if(!empty($welcomeModal))
<div id="welcomeModalBackdrop" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in">
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
            <p class="text-sm text-gray-600">
                @if($welcomeModal['type'] === 'pending_order')
                    Akun Anda sudah aktif. Berikut produk yang sebelumnya Anda pesan — lanjutkan pembayarannya untuk mengakses produk.
                @else
                    Akun Anda sudah aktif. Berikut produk yang sebelumnya ingin Anda beli — klik tombol di bawah untuk menyelesaikan pembelian.
                @endif
            </p>

            <div class="flex gap-4 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                @php
                    $thumb = $welcomeModal['product']->thumbnail ?? null;
                @endphp
                @if($thumb)
                    <img src="{{ asset('storage/' . $thumb) }}" alt="{{ $welcomeModal['product']->title }}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                @else
                    <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex-shrink-0"></div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $welcomeModal['product']->title }}</p>
                    <p class="mt-1 text-base font-bold text-indigo-600">Rp {{ number_format($welcomeModal['product']->price, 0, ',', '.') }}</p>
                    @if($welcomeModal['type'] === 'pending_order')
                        <p class="mt-1 text-xs text-gray-500">Order #{{ $welcomeModal['order']->id }} &middot; {{ $welcomeModal['order']->created_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if($welcomeModal['type'] === 'pending_order' && $welcomeModal['order']->payment_proof)
                <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
                    Bukti pembayaran sudah diupload. Pesanan sedang menunggu konfirmasi admin.
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ $welcomeModal['cta_url'] }}" class="flex-1 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-3 rounded-lg shadow">
                    {{ $welcomeModal['cta_label'] }}
                </a>
                <button type="button" onclick="document.getElementById('welcomeModalBackdrop').remove()" class="flex-1 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-3 rounded-lg">
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
