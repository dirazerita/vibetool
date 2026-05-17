@extends('layouts.dashboard')
@section('title', 'Kuponku')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Kuponku</h1>

{{-- Kupon yang di-assign ke member --}}
@if($assignedCoupons->count() > 0)
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kupon Khusus Saya</h2>
    <div class="space-y-4">
        @foreach($assignedCoupons as $coupon)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex flex-col sm:flex-row">
                {{-- Bagian kiri: kode kupon --}}
                <div class="sm:w-48 flex-shrink-0 bg-indigo-600 text-white p-5 flex flex-col items-center justify-center">
                    <div class="text-xs font-medium uppercase tracking-wider opacity-75">Kode Kupon</div>
                    <div class="text-xl font-bold mt-1 tracking-wider">{{ $coupon->code }}</div>
                    <div class="mt-2 text-2xl font-bold">
                        @if($coupon->discount_type === 'percent')
                            {{ rtrim(rtrim(number_format($coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                        @else
                            Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                        @endif
                    </div>
                    <div class="text-xs opacity-75">diskon</div>
                </div>
                {{-- Bagian kanan: detail --}}
                <div class="flex-1 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $coupon->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Diskon {{ $coupon->discount_type === 'percent' ? 'persentase' : 'nominal tetap' }}
                                @if($coupon->min_purchase > 0)
                                    &middot; Min. pembelian Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                        @if($coupon->is_active && (!$coupon->expired_at || !$coupon->expired_at->isPast()) && (!$coupon->max_uses || $coupon->used_count < $coupon->max_uses))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                        @elseif($coupon->expired_at && $coupon->expired_at->isPast())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Kedaluwarsa</span>
                        @elseif($coupon->max_uses && $coupon->used_count >= $coupon->max_uses)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Kuota Habis</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Nonaktif</span>
                        @endif
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Berlaku hingga:</span>
                            <span class="font-medium text-gray-900">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y') : 'Tidak terbatas' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Penggunaan:</span>
                            <span class="font-medium text-gray-900">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</span>
                        </div>
                    </div>

                    @if($coupon->products->count() > 0)
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">Berlaku untuk:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @foreach($coupon->products as $product)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $product->title }}</span>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">Berlaku untuk:</span>
                        <span class="text-sm font-medium text-gray-900">Semua produk</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Kupon global (untuk semua member) --}}
@if($globalCoupons->count() > 0)
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kupon Umum</h2>
    <p class="text-sm text-gray-500 mb-4">Kupon ini tersedia untuk semua member.</p>
    <div class="space-y-4">
        @foreach($globalCoupons as $coupon)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex flex-col sm:flex-row">
                {{-- Bagian kiri: kode kupon --}}
                <div class="sm:w-48 flex-shrink-0 bg-emerald-600 text-white p-5 flex flex-col items-center justify-center">
                    <div class="text-xs font-medium uppercase tracking-wider opacity-75">Kode Kupon</div>
                    <div class="text-xl font-bold mt-1 tracking-wider">{{ $coupon->code }}</div>
                    <div class="mt-2 text-2xl font-bold">
                        @if($coupon->discount_type === 'percent')
                            {{ rtrim(rtrim(number_format($coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                        @else
                            Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                        @endif
                    </div>
                    <div class="text-xs opacity-75">diskon</div>
                </div>
                {{-- Bagian kanan: detail --}}
                <div class="flex-1 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $coupon->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Diskon {{ $coupon->discount_type === 'percent' ? 'persentase' : 'nominal tetap' }}
                                @if($coupon->min_purchase > 0)
                                    &middot; Min. pembelian Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Berlaku hingga:</span>
                            <span class="font-medium text-gray-900">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y') : 'Tidak terbatas' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Penggunaan:</span>
                            <span class="font-medium text-gray-900">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</span>
                        </div>
                    </div>

                    @if($coupon->products->count() > 0)
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">Berlaku untuk:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @foreach($coupon->products as $product)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $product->title }}</span>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">Berlaku untuk:</span>
                        <span class="text-sm font-medium text-gray-900">Semua produk</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Empty state --}}
@if($assignedCoupons->count() === 0 && $globalCoupons->count() === 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
    <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada kupon</h3>
    <p class="mt-2 text-sm text-gray-500">Saat ini tidak ada kupon yang tersedia untuk akun Anda.</p>
</div>
@endif

@endsection
