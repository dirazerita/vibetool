@extends('layouts.dashboard')
@section('title', 'Pembelian Saya')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Pembelian Saya</h1>

@if($purchases->total() === 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <p class="text-gray-500 mb-4">Kamu belum pernah membeli produk apa pun.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
            Lihat Produk
        </a>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($purchases as $order)
            @php
                $product = $order->product;
                $lp = $product?->landingPage;
                $heroImage = $lp && $lp->hero_image ? asset('storage/' . $lp->hero_image) : null;
                $hasFile = $product && ($product->file_path || $product->file_url);
                $isPendingManual = $order->status === 'pending' && $order->payment_method === 'manual';
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                <div class="h-40 bg-gray-100 relative">
                    @if($heroImage)
                        <img src="{{ $heroImage }}" alt="{{ $product->title ?? 'Produk' }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    @endif
                    @if($isPendingManual)
                        <span class="absolute top-2 left-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                            {{ $order->payment_proof ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran' }}
                        </span>
                    @endif
                </div>

                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-900 truncate mb-1">{{ $product->title ?? 'Produk telah dihapus' }}</h3>
                    <p class="text-lg font-bold text-indigo-600 mb-1">Rp {{ number_format($order->amount, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mb-4">{{ $isPendingManual ? 'Dipesan' : 'Dibeli' }} {{ $order->created_at->format('d M Y H:i') }}</p>

                    <div class="mt-auto space-y-2">
                        @if($isPendingManual)
                            <a href="{{ route('checkout.manual', $order->id) }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                {{ $order->payment_proof ? 'Lihat Status Pembayaran' : 'Lanjutkan Pembayaran' }}
                            </a>
                            <form method="POST" action="{{ route('checkout.manual.cancel', $order->id) }}" onsubmit="return confirm('Batalkan pesanan #{{ $order->id }}? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf
                                <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Batalkan Pesanan
                                </button>
                            </form>
                        @elseif($hasFile && $order->download_token)
                            @php $isExternal = (bool) ($product->file_url ?? null); @endphp
                            <a href="{{ route('download', $order->download_token) }}" @if($isExternal) target="_blank" rel="noopener" @endif class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors">
                                @if($isExternal)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    Buka Link Produk
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Produk
                                @endif
                            </a>
                        @else
                            <button disabled class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed">
                                File belum tersedia
                            </button>
                        @endif

                        @if($product && ($product->product_type ?? null) === 'software' && $order->status === 'paid')
                            <a href="{{ route('dashboard.licenses') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                Lihat Lisensi
                            </a>
                        @endif

                        @if($product)
                            <a href="{{ route('product.show', $product->slug) }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Lihat Detail Produk
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $purchases->links() }}</div>
@endif
@endsection
