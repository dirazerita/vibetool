@extends('layouts.admin')
@section('title', 'Bagian Pembuat Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold dk-heading">Bagian Pembuat Produk</h1>
</div>

<p class="text-sm dk-text-muted mb-4">
    Atur persentase bagian yang diterima oleh member <strong>pembuat produk</strong> setiap kali produknya terjual.
    Bagian ini <strong>selalu dibayar</strong> ke pembuat (selain komisi affiliate &amp; bonus upline yang sudah ada), terlepas dari siapa yang menjadi affiliate/upline pada order tersebut.
    Set <code>0%</code> untuk skip (tidak ada pembayaran ke pembuat).
</p>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.creator-shares.index') }}" class="mb-4">
    <div class="flex gap-3 items-center">
        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari produk atau nama pembuat..." class="dk-input" style="max-width:360px">
        <button type="submit" class="dk-btn dk-btn-outline">Cari</button>
        @if(!empty($search))
            <a href="{{ route('admin.creator-shares.index') }}" class="text-sm dk-text-muted">Reset</a>
        @endif
    </div>
</form>

<div class="dk-table">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembuat</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bagian Pembuat (%)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status Produk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium dk-heading">{{ $product->title }}</div>
                        <div class="dk-text-muted text-xs">{{ $product->slug }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($product->creator)
                            <div class="font-medium dk-heading">{{ $product->creator->name }}</div>
                            <div class="dk-text-muted text-xs">{{ $product->creator->email }}</div>
                        @else
                            <span style="color:#4a5568">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right dk-text-muted">
                        @if((float) $product->price > 0)
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        @else
                            <span style="color:#4a5568">Gratis</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <form method="POST" action="{{ route('admin.creator-shares.update', $product) }}" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            @if($search ?? false)
                                <input type="hidden" name="q" value="{{ $search }}">
                            @endif
                            @if(request('page'))
                                <input type="hidden" name="page" value="{{ request('page') }}">
                            @endif
                            <input
                                type="number"
                                name="creator_share_percent"
                                step="0.01"
                                min="0"
                                max="100"
                                value="{{ (float) ($product->creator_share_percent ?? 0) }}"
                                class="dk-input"
                                style="width:90px;text-align:right"
                                required>
                            <span class="dk-text-muted">%</span>
                            <button type="submit" class="dk-btn dk-btn-primary" style="padding:6px 14px;font-size:13px">Simpan</button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($product->approval_status === 'approved')
                            <span class="inline-block px-2 py-1 rounded text-xs" style="background:rgba(34,197,94,0.15);color:#86efac">Approved</span>
                        @elseif($product->approval_status === 'pending')
                            <span class="inline-block px-2 py-1 rounded text-xs" style="background:rgba(234,179,8,0.15);color:#fde047">Pending</span>
                        @elseif($product->approval_status === 'rejected')
                            <span class="inline-block px-2 py-1 rounded text-xs" style="background:rgba(239,68,68,0.15);color:#fca5a5">Rejected</span>
                        @else
                            <span class="inline-block px-2 py-1 rounded text-xs" style="background:rgba(100,116,139,0.15);color:#cbd5e1">{{ $product->approval_status ?? '—' }}</span>
                        @endif
                        @if(!$product->is_active)
                            <span class="inline-block px-2 py-1 rounded text-xs ml-1" style="background:rgba(100,116,139,0.15);color:#cbd5e1">Nonaktif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">
                        Belum ada produk yang di-upload oleh member.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
