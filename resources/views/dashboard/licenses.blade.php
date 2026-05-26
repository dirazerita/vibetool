@extends('layouts.dashboard')
@section('title', 'Lisensi Saya')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-2">Lisensi Saya</h1>
<p class="text-sm dk-text mb-6">Kunci lisensi untuk software / tool yang sudah kamu beli.</p>

@if($licenses->isEmpty() && $pendingOrders->isEmpty())
    <div class="dk-card p-10 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
        </svg>
        <p class="dk-text-muted mb-2">Kamu belum memiliki lisensi.</p>
        <p class="text-xs " style="color:#4a5568">Lisensi akan otomatis diberikan saat kamu membeli produk bertipe software / tool.</p>
    </div>
@else

@if($pendingOrders->isNotEmpty())
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-semibold text-yellow-900 mb-2">Lisensi belum tersedia</h2>
    <p class="text-xs text-yellow-800 mb-3">Pembayaran kamu sudah dikonfirmasi. Lisensi sedang diproses dan akan segera tersedia.</p>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="flex items-center justify-between dk-card rounded-lg border border-yellow-200 p-3">
            <div class="text-sm">
                <div class="font-medium dk-heading">{{ $order->product->title ?? '—' }}</div>
                <div class="text-xs dk-text">Order #{{ $order->id }} · {{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-900">Menunggu Lisensi</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($licenses->isNotEmpty())
<div class="gap-6" style="display:grid;grid-template-columns:repeat(3,1fr)">
    @foreach($licenses as $license)
        @php
            $product = $license->product;
            $lp = $product?->landingPage;
            $cardImage = null;
            if ($product && $product->thumbnail) {
                $cardImage = asset('storage/' . $product->thumbnail);
            } elseif ($lp && $lp->hero_image) {
                $cardImage = asset('storage/' . $lp->hero_image);
            }
        @endphp
        <div class="dk-table hover:shadow-lg transition-shadow duration-200 flex flex-col">
            {{-- Thumbnail 1:1 --}}
            <div style="background:#151e2d; position:relative;aspect-ratio: 1 / 1;">
                @if($cardImage)
                    <img src="{{ $cardImage }}" alt="{{ $product->title ?? 'Produk' }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 flex items-center justify-center p-4">
                        <span class="text-white text-base font-bold text-center leading-snug drop-shadow-md">{{ $product->title ?? 'Produk telah dihapus' }}</span>
                    </div>
                @endif

                {{-- Badge status di pojok kiri atas --}}
                <div class="absolute top-2 left-2 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm bg-purple-500 text-white">
                        Software / Tool
                    </span>
                    @if($license->isLifetime())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm bg-green-500 text-white">Lifetime</span>
                    @elseif($license->isExpired())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm bg-red-500 text-white">Kedaluwarsa</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm bg-blue-500 text-white">Aktif</span>
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4 flex flex-col flex-1">
                <h3 class="text-lg font-bold dk-heading truncate">{{ $product->title ?? 'Produk telah dihapus' }}</h3>
                <p class="text-xs mb-" style="color:#64748b3">Order #{{ $license->order_id }} · {{ $license->assigned_at?->format('d M Y H:i') ?? $license->created_at->format('d M Y H:i') }}</p>

                @if(!$license->isLifetime() && $license->expires_at)
                    <p class="text-xs dk-text-muted mb-3">
                        @if($license->isExpired())
                            Kedaluwarsa: <span class="font-medium text-red-600">{{ $license->expires_at->format('d M Y') }}</span>
                        @else
                            Aktif s/d: <span class="font-medium dk-text">{{ $license->expires_at->format('d M Y') }}</span>
                        @endif
                    </p>
                @endif

                <label class="block text-[11px] font-semibold dk-text-muted uppercase tracking-wide mb-1">Kunci Lisensi</label>
                <div class="flex items-stretch gap-2 mb-3">
                    <input type="text" readonly value="{{ $license->key }}" id="license-{{ $license->id }}" class="flex-1 min-w-0 border dk-input rounded-lg px-3 py-2 text-xs font-mono " style="background:#151e2d dk-heading focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" onclick="copyLicense('license-{{ $license->id }}', this)" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-xs font-medium whitespace-nowrap">Salin</button>
                </div>

                @if($license->extra_info)
                    <div style="background:#151e2d;border:1px solid #2d3a4a;border-radius:8px;padding:12px;margin-bottom:12px;">
                        <div class="text-[11px] font-semibold dk-text uppercase tracking-wide mb-1">Instruksi Aktivasi</div>
                        <div class="text-xs dk-text whitespace-pre-line">{{ $license->extra_info }}</div>
                    </div>
                @endif

                {{-- Action buttons di footer kartu --}}
                <div class="flex flex-wrap gap-1.5 pt-2 dk-divider mt-auto">
                    @if($product && ($product->file_url || $product->file_path) && $license->order && $license->order->download_token)
                        @php $isExternal = (bool) $product->file_url; @endphp
                        <a href="{{ route('download', $license->order->download_token) }}" {{ $isExternal ? 'target=_blank rel=noopener' : '' }} class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium transition-colors" style="color:#818cf8">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            {{ $isExternal ? 'Buka Link Produk' : 'Download' }}
                        </a>
                    @endif
                    @if($product)
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium transition-colors" style="color:#94a3b8">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Detail Produk
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endif

<script>
    function copyLicense(id, btn) {
        const input = document.getElementById(id);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const old = btn.textContent;
            btn.textContent = 'Tersalin!';
            btn.classList.add('bg-emerald-600');
            btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            setTimeout(() => {
                btn.textContent = old;
                btn.classList.remove('bg-emerald-600');
                btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            }, 1500);
        });
    }
</script>
@endsection
