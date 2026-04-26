@extends('layouts.dashboard')
@section('title', 'Produk')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Produk</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($products as $product)
        @php
            $lp = $product->landingPage;
            $heroImage = $lp && $lp->hero_image ? asset('storage/' . $lp->hero_image) : null;
            $affiliateLink = url('/p/' . $product->slug . '?ref=' . $user->referral_code);
            $commissionAmount = $product->price * $product->commission_percent / 100;
            $uplineAmount = $product->price * $product->upline_percent / 100;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Thumbnail --}}
            <div class="h-40 bg-gray-100">
                @if($heroImage)
                    <img src="{{ $heroImage }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-900 truncate mb-1">{{ $product->title }}</h3>
                <p class="text-lg font-bold text-indigo-600 mb-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                <p class="text-sm text-green-600 font-medium">Komisi kamu: Rp {{ number_format($commissionAmount, 0, ',', '.') }} per penjualan</p>
                <p class="text-xs text-purple-500 mb-4">Bonus upline: Rp {{ number_format($uplineAmount, 0, ',', '.') }} per penjualan downline</p>

                {{-- Buttons --}}
                <div class="space-y-2">
                    <a href="{{ $affiliateLink }}" target="_blank" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Lihat Landing Page
                    </a>
                    <button onclick="copyLink('{{ $affiliateLink }}', this)" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        Salin Link Afiliasi
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Link Promosi Section --}}
@if(count($promoProducts) > 0)
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">
        <svg class="w-5 h-5 inline-block text-indigo-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        Link Promosi Saya (dengan Kupon)
    </h2>
    <p class="text-sm text-gray-500 mb-4">Bagikan link ini — kupon otomatis diterapkan saat calon pembeli checkout.</p>
    <div class="space-y-3">
        @foreach($promoProducts as $promo)
            @php
                $promoLink = url('/p/' . $promo['product']->slug . '?ref=' . $user->referral_code);
                $discountLabel = $promo['coupon']->discount_type === 'percent'
                    ? $promo['coupon']->discount_value . '%'
                    : 'Rp ' . number_format($promo['coupon']->discount_value, 0, ',', '.');
            @endphp
            <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $promo['product']->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Kupon: <span class="font-mono text-indigo-600">{{ $promo['coupon']->code }}</span> — diskon {{ $discountLabel }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $promoLink }}" class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 w-48 sm:w-64">
                        <button onclick="copyLink('{{ $promoLink }}', this)" class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-xs font-medium transition-colors whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            Salin
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Downline Section --}}
<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Downline kamu ({{ $downlines->count() }} orang)</h2>

    @if($downlines->count() > 0)
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach($downlines->take(5) as $downline)
                <div class="flex items-center gap-2 bg-gray-50 rounded-full px-3 py-1.5">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold text-xs">{{ strtoupper(substr($downline->name, 0, 1)) }}</span>
                    </div>
                    <span class="text-sm text-gray-700">{{ $downline->name }}</span>
                </div>
            @endforeach
            @if($downlines->count() > 5)
                <div class="flex items-center px-3 py-1.5">
                    <span class="text-sm text-gray-500">+{{ $downlines->count() - 5 }} lainnya</span>
                </div>
            @endif
        </div>
    @else
        <p class="text-sm text-gray-500 mb-4">Belum ada downline. Ajak teman bergabung!</p>
    @endif

    @php
        $registerLink = url('/register?ref=' . $user->referral_code);
    @endphp
    <div class="border-t border-gray-100 pt-4">
        <p class="text-sm text-gray-600 mb-2">Link ajak teman:</p>
        <div class="flex items-center gap-2">
            <input type="text" readonly value="{{ $registerLink }}" class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
            <button onclick="copyLink('{{ $registerLink }}', this)" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                Salin
            </button>
        </div>
    </div>
</div>

<script>
function copyLink(link, btn) {
    navigator.clipboard.writeText(link).then(function() {
        var originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Link berhasil disalin!';
        btn.classList.remove('bg-gray-100', 'text-gray-700', 'bg-indigo-600');
        btn.classList.add('bg-green-100', 'text-green-700');
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('bg-green-100', 'text-green-700');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        }, 2000);
    });
}
</script>
@endsection
