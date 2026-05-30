@extends('layouts.dashboard')
@section('title', 'Produk')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Produk</h1>

<div class="gap-6" style="display:grid;grid-template-columns:repeat(3,1fr)">
    @foreach($products as $product)
        @php
            $lp = $product->landingPage;
            $cardImage = null;
            if ($product->thumbnail) {
                $cardImage = asset('storage/' . $product->thumbnail);
            } elseif ($lp && $lp->hero_image) {
                $cardImage = asset('storage/' . $lp->hero_image);
            }
            $affiliateLink = url('/p/' . $product->slug . '?ref=' . $user->referral_code);
            $status = $purchaseStatus[$product->id] ?? null;
            $alreadyPaid = $status['paid'] ?? false;
            $pendingOrder = $status['pending_order'] ?? null;
            $commissionPercent = $alreadyPaid ? (float) $product->commission_percent : (float) ($product->commission_percent_non_owner ?? $product->commission_percent);
            $uplinePercent = $alreadyPaid ? (float) $product->upline_percent : (float) ($product->upline_percent_non_owner ?? $product->upline_percent);
            $commissionAmount = $product->price * $commissionPercent / 100;
            $uplineAmount = $product->price * $uplinePercent / 100;
            $isMyProduct = $product->created_by && (int) $product->created_by === (int) $user->id;
            $creatorSharePercent = (float) ($product->creator_share_percent ?? 0);
            $creatorShareAmount = $product->price * $creatorSharePercent / 100;
        @endphp
        <div class="dk-table">
            {{-- Thumbnail --}}
            <div style="background:#151e2d; position:relative; aspect-ratio: 1 / 1;">
                @if($cardImage)
                    <img src="{{ $cardImage }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="p-4">
                <h3 class="text-lg font-bold dk-heading truncate mb-1">{{ $product->title }}</h3>
                @if($product->isFree())
                    <p class="text-lg font-bold mb-1" style="color:#10b981">GRATIS</p>
                    <p class="text-xs dk-text-muted mb-3">Klaim langsung — login software pakai email + password akun VibeTool.Id kamu.</p>
                @else
                    <p class="text-lg font-bold text-indigo-600 mb-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    <p class="text-sm text-green-600 font-medium">Komisi kamu: Rp {{ number_format($commissionAmount, 0, ',', '.') }} per penjualan <span class="text-xs dk-text-muted font-normal">({{ rtrim(rtrim(number_format($commissionPercent, 2, '.', ''), '0'), '.') }}%)</span></p>
                    <p class="text-xs text-purple-500">Bonus upline: Rp {{ number_format($uplineAmount, 0, ',', '.') }} per penjualan downline</p>
                    @if($isMyProduct && $creatorSharePercent > 0)
                        <p class="text-xs mt-1" style="color:#fbbf24">
                            <svg class="inline w-3.5 h-3.5 mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            Bagian Pembuat: Rp {{ number_format($creatorShareAmount, 0, ',', '.') }} per penjualan <span class="text-xs dk-text-muted font-normal">({{ rtrim(rtrim(number_format($creatorSharePercent, 2, '.', ''), '0'), '.') }}%)</span>
                        </p>
                    @elseif($isMyProduct)
                        <p class="text-xs mt-1 dk-text-muted">
                            <svg class="inline w-3.5 h-3.5 mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Bagian Pembuat: belum diatur admin
                        </p>
                    @endif
                    @if(!$alreadyPaid && !$isMyProduct && ($product->commission_percent_non_owner ?? $product->commission_percent) != $product->commission_percent)
                        <p class="text-xs text-amber-600 mt-1 mb-3"><svg class="inline w-3.5 h-3.5 mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Beli produk ini untuk dapat komisi lebih besar ({{ rtrim(rtrim(number_format((float) $product->commission_percent, 2, '.', ''), '0'), '.') }}%)</p>
                    @else
                        <div class="mb-3"></div>
                    @endif
                @endif

                {{-- Buttons --}}
                <div class="space-y-2">
                    @if($isMyProduct)
                        <div class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium"
                             style="background-color:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); color:#fbbf24;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            Produk Saya
                        </div>
                    @elseif($alreadyPaid)
                        <div class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-medium"
                             style="background-color:#ecfdf5; border:1px solid #a7f3d0; color:#047857;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $product->isFree() ? 'Sudah Diklaim' : 'Sudah Dibeli' }}
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
                    @elseif($product->isFree())
                        <form method="POST" action="{{ route('free.claim', $product->slug) }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                                    style="background-color:#10b981; color:#ffffff;"
                                    onmouseover="this.style.backgroundColor='#059669'"
                                    onmouseout="this.style.backgroundColor='#10b981'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Dapatkan Gratis
                            </button>
                        </form>
                    @else
                        <a href="{{ route('checkout', $product->slug) }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                           class="dk-btn dk-btn-success" style="
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
                    <button onclick="copyLink('{{ $affiliateLink }}', this)" class="flex items-center justify-center gap-2 w-full px-4 py-2 " style="background:#151e2d dk-text rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
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
<div class="mt-8 dk-card" style="padding:24px;">
    <h2 class="text-lg font-semibold dk-heading mb-4">
        <svg class="w-5 h-5 inline-block text-indigo-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        Link Promosi Saya (dengan Kupon)
    </h2>
    <p class="text-sm dk-text-muted mb-4">Bagikan link ini — kupon otomatis diterapkan saat calon pembeli checkout.</p>
    <div class="space-y-3">
        @foreach($promoProducts as $promo)
            @php
                $promoLink = url('/p/' . $promo['product']->slug . '?ref=' . $user->referral_code);
                $discountLabel = $promo['coupon']->discount_type === 'percent'
                    ? $promo['coupon']->discount_value . '%'
                    : 'Rp ' . number_format($promo['coupon']->discount_value, 0, ',', '.');
            @endphp
            <div class="border border-gray-100 rounded-lg p-4 hover:" style="background:#151e2d transition-colors">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium dk-heading truncate">{{ $promo['product']->title }}</p>
                        <p class="text-xs dk-text-muted mt-0.5">Kupon: <span class="font-mono text-indigo-600">{{ $promo['coupon']->code }}</span> — diskon {{ $discountLabel }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $promoLink }}" class="text-xs " style="background:#151e2d;border:1px solid #2d3a4a;border-radius:8px;padding:8px 12px;width:256px;color:#a5b4fc;">
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
    <div class="mt-8 dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Upline kamu</h2>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 font-semibold text-base">{{ strtoupper(substr($user->upline->name, 0, 1)) }}</span>
                </div>
                <div>
                    <div class="text-sm font-semibold dk-heading">{{ $user->upline->name }}</div>
                    <div class="dk-text-muted" style="font-size:12px">
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
<div class="mt-8 dk-card" style="padding:24px;">
    <h2 class="text-lg font-semibold dk-heading mb-4">Downline kamu ({{ $downlines->count() }} orang)</h2>

    @if($downlines->count() > 0)
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach($downlines->take(5) as $downline)
                <div class="flex items-center gap-2 " style="background:#151e2d rounded-full px-3 py-1.5">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold text-xs">{{ strtoupper(substr($downline->name, 0, 1)) }}</span>
                    </div>
                    <span class="text-sm dk-text">{{ $downline->name }}</span>
                </div>
            @endforeach
            @if($downlines->count() > 5)
                <div class="flex items-center px-3 py-1.5">
                    <span class="dk-text-muted" style="font-size:14px">+{{ $downlines->count() - 5 }} lainnya</span>
                </div>
            @endif
        </div>
    @else
        <p class="text-sm dk-text-muted mb-4">Belum ada downline. Ajak teman bergabung!</p>
    @endif

    @php
        $registerLink = url('/register?ref=' . $user->referral_code);
    @endphp
    <div class="dk-divider pt-4">
        <p class="text-sm dk-text mb-2">Link ajak teman:</p>
        <div class="flex items-center gap-2">
            <input type="text" readonly value="{{ $registerLink }}" class="flex-1 text-xs " style="background:#151e2d;border:1px solid #2d3a4a;border-radius:8px;padding:8px 12px;color:#a5b4fc;">
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
        btn.classList.remove('" style="background:#151e2d', 'text-gray-700', 'bg-indigo-600');
        btn.classList.add('bg-green-100', 'text-green-700');
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('bg-green-100', 'text-green-700');
            btn.classList.add('" style="background:#151e2d', 'text-gray-700');
        }, 2000);
    });
}
</script>
@endsection
