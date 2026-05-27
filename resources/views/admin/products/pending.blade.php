@extends('layouts.admin')
@section('title', 'Produk Menunggu Persetujuan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold dk-heading">Produk Menunggu Persetujuan</h1>
    <a href="{{ route('admin.products.index') }}" class="dk-btn dk-btn-outline">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Kelola Produk
    </a>
</div>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif

@if($products->count() > 0)
<div class="space-y-4">
    @foreach($products as $product)
        <div class="dk-card" style="padding:20px;">
            <div class="flex gap-4" style="display:flex; align-items:flex-start;">
                {{-- Thumbnail --}}
                <div style="width:120px; height:120px; flex-shrink:0; border-radius:8px; overflow:hidden; background:#151e2d;">
                    @if($product->thumbnail)
                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div style="flex:1; min-width:0;">
                    <div class="flex items-center gap-2 mb-1" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <h3 class="dk-heading" style="font-size:1.125rem; font-weight:700;">{{ $product->title }}</h3>
                        @php
                            $typeBadge = match($product->product_type) {
                                'software' => ['Software / Lisensi', '#8b5cf6'],
                                'free' => ['Produk Gratis', '#10b981'],
                                default => ['Produk Digital', '#f59e0b'],
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold" style="background:{{ $typeBadge[1] }};color:#fff">{{ $typeBadge[0] }}</span>
                    </div>
                    <p class="dk-text-muted" style="font-size:13px; margin-bottom:4px;">
                        Disubmit oleh: <strong style="color:#a5b4fc">{{ $product->creator->name ?? 'Unknown' }}</strong>
                        ({{ $product->creator->email ?? '' }})
                        &middot; {{ $product->created_at->diffForHumans() }}
                    </p>
                    @if($product->isFree())
                        <p style="font-weight:700;color:#10b981; font-size:14px;">GRATIS</p>
                    @else
                        <p style="font-weight:700;color:#6ee7b7; font-size:14px;">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    @endif
                    @if($product->description)
                        <p class="dk-text" style="font-size:13px; margin-top:4px; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $product->description }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="flex-shrink:0; display:flex; flex-direction:column; gap:8px;">
                    <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                        @csrf
                        <button type="submit" class="dk-btn dk-btn-success" style="width:100%;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Setujui
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-form-{{ $product->id }}').style.display = document.getElementById('reject-form-{{ $product->id }}').style.display === 'none' ? '' : 'none'" class="dk-btn dk-btn-danger" style="width:100%;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Tolak
                    </button>
                    <a href="{{ route('admin.products.edit', $product) }}" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="dk-btn dk-btn-outline" style="width:100%; color:#fca5a5; justify-content:center;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

            {{-- Reject form (hidden by default) --}}
            <div id="reject-form-{{ $product->id }}" style="display:none; margin-top:16px; padding-top:16px; border-top:1px solid #2d3a4a;">
                <form method="POST" action="{{ route('admin.products.reject', $product) }}">
                    @csrf
                    <label class="dk-label">Alasan Penolakan (opsional)</label>
                    <textarea name="rejection_reason" rows="2" class="w-full dk-input" placeholder="Tuliskan alasan penolakan agar member bisa memperbaiki..."></textarea>
                    <div class="mt-2 flex gap-2">
                        <button type="submit" class="dk-btn dk-btn-danger">Konfirmasi Tolak</button>
                        <button type="button" onclick="this.closest('[id^=reject-form]').style.display='none'" class="dk-btn dk-btn-outline">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $products->links() }}</div>
@else
<div class="dk-card" style="padding:48px; text-align:center;">
    <svg class="mx-auto mb-4" style="width:48px;height:48px;color:#10b981" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <p class="dk-text" style="font-size:16px; margin-bottom:8px;">Tidak ada produk yang menunggu persetujuan</p>
    <p class="dk-text-muted" style="font-size:14px;">Semua produk member sudah ditinjau.</p>
</div>
@endif
@endsection
