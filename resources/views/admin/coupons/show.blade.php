@extends('layouts.admin')
@section('title', 'Detail Kupon')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Detail Kupon: {{ $coupon->code }}</h1>
    <div class="flex gap-3">
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Edit</a>
        <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Kembali</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kupon</h2>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Kode</dt>
                <dd class="text-sm font-mono font-medium text-indigo-600">{{ $coupon->code }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Nama</dt>
                <dd class="text-sm font-medium text-gray-900">{{ $coupon->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Diskon</dt>
                <dd class="text-sm font-medium text-gray-900">
                    @if($coupon->discount_type === 'percent')
                        {{ $coupon->discount_value }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Min. Pembelian</dt>
                <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Penggunaan</dt>
                <dd class="text-sm font-medium text-gray-900">{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Expired</dt>
                <dd class="text-sm font-medium text-gray-900">{{ $coupon->expired_at ? $coupon->expired_at->format('d M Y H:i') : 'Tidak ada' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-sm text-gray-600">Status</dt>
                <dd>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Member yang Dapat Menggunakan</h2>
            @if($coupon->members->count() > 0)
                <ul class="space-y-2">
                    @foreach($coupon->members as $member)
                        <li class="text-sm text-gray-700">{{ $member->name }} <span class="text-gray-500">({{ $member->email }})</span></li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">Semua member dapat menggunakan kupon ini.</p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Produk yang Berlaku</h2>
            @if($coupon->products->count() > 0)
                <ul class="space-y-2">
                    @foreach($coupon->products as $product)
                        <li class="text-sm text-gray-700">{{ $product->title }} <span class="text-gray-500">- Rp {{ number_format($product->price, 0, ',', '.') }}</span></li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">Kupon berlaku untuk semua produk.</p>
            @endif
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Riwayat Penggunaan</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diskon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Bayar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($usedOrders as $order)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-red-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada yang menggunakan kupon ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
