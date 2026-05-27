@extends('layouts.admin')
@section('title', 'Detail Kupon')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold dk-heading">Detail Kupon: {{ $coupon->code }}</h1>
    <div class="flex gap-3">
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="dk-btn dk-btn-primary">Edit</a>
        <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 border dk-input rounded-lg dk-text hover:" style="background:#151e2d font-medium">Kembali</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Informasi Kupon</h2>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Kode</dt>
                <dd class="text-sm font-mono font-medium text-indigo-600">{{ $coupon->code }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Nama</dt>
                <dd class="text-sm font-medium dk-heading">{{ $coupon->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Diskon</dt>
                <dd class="text-sm font-medium dk-heading">
                    @if($coupon->discount_type === 'percent')
                        {{ $coupon->discount_value }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Min. Pembelian</dt>
                <dd class="text-sm font-medium dk-heading">Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Penggunaan</dt>
                <dd class="text-sm font-medium dk-heading">{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Expired</dt>
                <dd class="text-sm font-medium dk-heading">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y H:i') : 'Tidak ada' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="dk-text" style="font-size:14px">Status</dt>
                <dd>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"' }}">
                        {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="space-y-6">
        <div class="dk-card" style="padding:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-3">Member yang Dapat Menggunakan</h2>
            @if($coupon->members->count() > 0)
                <ul class="space-y-2">
                    @foreach($coupon->members as $member)
                        <li class="text-sm dk-text">{{ $member->name }} <span class="dk-text-muted">({{ $member->email }})</span></li>
                    @endforeach
                </ul>
            @else
                <p class="dk-text-muted" style="font-size:14px">Semua member dapat menggunakan kupon ini.</p>
            @endif
        </div>

        <div class="dk-card" style="padding:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-3">Produk yang Berlaku</h2>
            @if($coupon->products->count() > 0)
                <ul class="space-y-2">
                    @foreach($coupon->products as $product)
                        <li class="text-sm dk-text">{{ $product->title }} <span class="dk-text-muted">- Rp {{ number_format($product->price, 0, ',', '.') }}</span></li>
                    @endforeach
                </ul>
            @else
                <p class="dk-text-muted" style="font-size:14px">Kupon berlaku untuk semua produk.</p>
            @endif
        </div>
    </div>
</div>

<div class="dk-table">
    <div class="p-6 " style="border-bottom:1px solid #1e2b3d">
        <h2 class="text-lg font-semibold dk-heading">Riwayat Penggunaan</h2>
    </div>
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Diskon</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Total Bayar</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usedOrders as $order)
            <tr>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $order->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-red-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $order->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">Belum ada yang menggunakan kupon ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
