@extends('layouts.dashboard')
@section('title', 'Lisensi Saya')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-2">Lisensi Saya</h1>
<p class="text-sm text-gray-600 mb-6">Kunci lisensi untuk software / tool yang sudah kamu beli.</p>

@if($licenses->isEmpty() && $pendingOrders->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
        </svg>
        <p class="text-gray-500 mb-2">Kamu belum memiliki lisensi.</p>
        <p class="text-xs text-gray-400">Lisensi akan otomatis diberikan saat kamu membeli produk bertipe software / tool.</p>
    </div>
@else

@if($pendingOrders->isNotEmpty())
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-semibold text-yellow-900 mb-2">Lisensi belum tersedia</h2>
    <p class="text-xs text-yellow-800 mb-3">Pembayaran kamu sudah dikonfirmasi. Lisensi sedang diproses dan akan segera tersedia.</p>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="flex items-center justify-between bg-white rounded-lg border border-yellow-200 p-3">
            <div class="text-sm">
                <div class="font-medium text-gray-900">{{ $order->product->title ?? '—' }}</div>
                <div class="text-xs text-gray-600">Order #{{ $order->id }} · {{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-900">Menunggu Lisensi</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($licenses->isNotEmpty())
<div class="space-y-4">
    @foreach($licenses as $license)
        @php
            $product = $license->product;
            $lp = $product?->landingPage;
            $heroImage = $lp && $lp->hero_image ? asset('storage/' . $lp->hero_image) : null;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-48 h-40 md:h-auto bg-gray-100 flex-shrink-0">
                    @if($heroImage)
                        <img src="{{ $heroImage }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="p-5 flex-1 min-w-0">
                    <div class="flex items-start justify-between flex-wrap gap-2 mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $product->title ?? 'Produk telah dihapus' }}</h3>
                            <p class="text-xs text-gray-500">Didapatkan {{ $license->assigned_at?->format('d M Y H:i') ?? $license->created_at->format('d M Y H:i') }} · Order #{{ $license->order_id }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Software / Tool</span>
                        @if($license->isLifetime())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Lifetime</span>
                        @elseif($license->isExpired())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Kedaluwarsa {{ $license->expires_at->format('d M Y') }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Aktif s/d {{ $license->expires_at->format('d M Y') }}</span>
                        @endif
                    </div>

                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Kunci Lisensi</label>
                    <div class="flex items-stretch gap-2 mb-3">
                        <input type="text" readonly value="{{ $license->key }}" id="license-{{ $license->id }}" class="flex-1 min-w-0 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="copyLicense('license-{{ $license->id }}', this)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium whitespace-nowrap">Salin</button>
                    </div>

                    @if($license->extra_info)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Instruksi Aktivasi:</div>
                            <div class="text-sm text-gray-700 whitespace-pre-line">{{ $license->extra_info }}</div>
                        </div>
                    @endif

                    @if($product && ($product->file_url || $product->file_path) && $license->order && $license->order->download_token)
                        @php $isExternal = (bool) $product->file_url; @endphp
                        <a href="{{ route('download', $license->order->download_token) }}" {{ $isExternal ? 'target=_blank rel=noopener' : '' }} class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            {{ $isExternal ? 'Buka Link Produk' : 'Download Produk' }}
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
