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
            $status = $purchaseStatus[$product->id] ?? null;
            $alreadyPaid = $status['paid'] ?? false;
            $pendingOrder = $status['pending_order'] ?? null;
            $commissionPercent = $alreadyPaid ? (float) $product->commission_percent : (float) ($product->commission_percent_non_owner ?? $product->commission_percent);
            $uplinePercent = $alreadyPaid ? (float) $product->upline_percent : (float) ($product->upline_percent_non_owner ?? $product->upline_percent);
            $commissionAmount = $product->price * $commissionPercent / 100;
            $uplineAmount = $product->price * $uplinePercent / 100;
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
                <p class="text-sm text-green-600 font-medium">Komisi kamu: Rp {{ number_format($commissionAmount, 0, ',', '.') }} per penjualan <span class="text-xs text-gray-500 font-normal">({{ rtrim(rtrim(number_format($commissionPercent, 2, '.', ''), '0'), '.') }}%)</span></p>
                <p class="text-xs text-purple-500">Bonus upline: Rp {{ number_format($uplineAmount, 0, ',', '.') }} per penjualan downline</p>
                @if(!$alreadyPaid && ($product->commission_percent_non_owner ?? $product->commission_percent) != $product->commission_percent)
                    <p class="text-xs text-amber-600 mt-1 mb-3"><svg class="inline w-3.5 h-3.5 mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Beli produk ini untuk dapat komisi lebih besar ({{ rtrim(rtrim(number_format((float) $product->commission_percent, 2, '.', ''), '0'), '.') }}%)</p>
                @else
                    <div class="mb-3"></div>
                @endif

                {{-- Buttons --}}
                <div class="space-y-2">
                    @if($alreadyPaid)
                        <div class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium"
                             style="background-color:#ecfdf5; border:1px solid #a7f3d0; color:#047857;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sudah Dibeli
                        </div>
                        <a href="{{ route('dashboard.purchases') }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                           style="background-color:#059669; color:#ffffff;"
                           onmouseover="this.style.backgroundColor='#047857'"
                           onmouseout="this.style.backgroundColor='#059669'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Akses di Pembelian Saya
                        </a>
                    @elseif($pendingOrder)
                        <a href="{{ route('checkout.manual', $pendingOrder->id) }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                           style="background-color:#eab308; color:#ffffff;"
                           onmouseover="this.style.backgroundColor='#ca8a04'"
                           onmouseout="this.style.backgroundColor='#eab308'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            {{ $pendingOrder->payment_proof ? 'Menunggu Konfirmasi Admin' : 'Lanjutkan Pembayaran' }}
                        </a>
                    @else
                        <a href="{{ route('checkout', $product->slug) }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                           style="background-color:#16a34a; color:#ffffff;"
                           onmouseover="this.style.backgroundColor='#15803d'"
                           onmouseout="this.style.backgroundColor='#16a34a'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Beli Sekarang
                        </a>
                    @endif
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

{{-- Upline Section --}}
@if($user->upline)
    @php
        $uplineWa = \App\Helpers\PhoneNumber::normalize($user->upline->whatsapp_number ?? null);
    @endphp
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upline kamu</h2>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-semibold text-base">{{ strtoupper(substr($user->upline->name, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ $user->upline->name }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $user->upline->email }}
                        @if($user->upline->whatsapp_number)
                            &middot; {{ $user->upline->whatsapp_number }}
                        @endif
                    </div>
                </div>
            </div>
            @if($uplineWa)
                <a href="https://wa.me/{{ $uplineWa }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.595 5.392l-.999 3.648 3.893-1.022zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                    Hubungi Upline
                </a>
            @endif
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
