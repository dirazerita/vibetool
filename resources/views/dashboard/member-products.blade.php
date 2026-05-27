@extends('layouts.dashboard')
@section('title', 'Produk Saya')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold dk-heading">Produk Saya</h1>
    <a href="{{ route('dashboard.member-products.create') }}" class="dk-btn dk-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Upload Produk Baru
    </a>
</div>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif

@if($products->count() > 0)
<div class="gap-6" style="display:grid;grid-template-columns:repeat(3,1fr)">
    @foreach($products as $product)
        @php
            $cardImage = $product->thumbnail ? asset('storage/' . $product->thumbnail) : null;
            $statusBadge = match($product->approval_status) {
                'pending' => ['Menunggu Persetujuan', '#f59e0b', 'rgba(245,158,11,0.15)'],
                'approved' => ['Disetujui', '#10b981', 'rgba(16,185,129,0.15)'],
                'rejected' => ['Ditolak', '#ef4444', 'rgba(239,68,68,0.15)'],
                default => ['Unknown', '#64748b', 'rgba(100,116,139,0.15)'],
            };
            $typeBadge = match($product->product_type) {
                'software' => ['Software / Lisensi', '#8b5cf6'],
                'free' => ['Produk Gratis', '#10b981'],
                default => ['Produk Digital', '#f59e0b'],
            };
        @endphp
        <div class="dk-table">
            <div style="background:#151e2d; position:relative; aspect-ratio:16/10;">
                @if($cardImage)
                    <img src="{{ $cardImage }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center p-4">
                        <span class="text-white text-base font-bold text-center leading-snug">{{ $product->title }}</span>
                    </div>
                @endif

                <div class="absolute top-2 left-2 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm" style="background:{{ $statusBadge[2] }};color:{{ $statusBadge[1] }}">
                        {{ $statusBadge[0] }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm" style="background:{{ $typeBadge[1] }};color:#fff">
                        {{ $typeBadge[0] }}
                    </span>
                </div>
            </div>

            <div class="p-4">
                <h3 class="dk-heading" style="font-size:1.125rem; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">{{ $product->title }}</h3>
                @if($product->isFree())
                    <p style="font-size:1.125rem;font-weight:700;color:#10b981" class="mb-2">GRATIS</p>
                @else
                    <p style="font-size:1.125rem;font-weight:700;color:#6ee7b7" class="mb-2">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                @endif

                @if($product->isRejected() && $product->rejection_reason)
                    <div class="dk-alert-error" style="padding:8px 12px; margin-bottom:8px; font-size:12px;">
                        <strong>Alasan ditolak:</strong> {{ $product->rejection_reason }}
                    </div>
                @endif

                <div class="flex flex-wrap gap-1.5 pt-2 dk-divider">
                    <a href="{{ route('dashboard.member-products.edit', $product) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium transition-colors" style="color:#818cf8">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('dashboard.member-products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-medium transition-colors" style="color:#fca5a5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $products->links() }}</div>
@else
<div class="dk-card" style="padding:48px; text-align:center;">
    <svg class="mx-auto mb-4" style="width:48px;height:48px;color:#64748b" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
    <p class="dk-text" style="font-size:16px; margin-bottom:8px;">Belum ada produk yang diupload</p>
    <p class="dk-text-muted" style="font-size:14px; margin-bottom:16px;">Mulai upload produk pertama Anda. Produk akan ditinjau admin sebelum dipublikasikan.</p>
    <a href="{{ route('dashboard.member-products.create') }}" class="dk-btn dk-btn-primary">Upload Produk Baru</a>
</div>
@endif
@endsection
