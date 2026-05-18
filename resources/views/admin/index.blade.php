@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Total Member</div>
            <div class="text-3xl font-bold text-indigo-600">{{ $totalMembers }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Total Produk</div>
            <div class="text-3xl font-bold text-purple-600">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Pesanan Sukses</div>
            <div class="text-3xl font-bold text-green-600">{{ $totalOrders }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
            <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Total Komisi Dibayar</div>
            <div class="text-3xl font-bold text-orange-600">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-center gap-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm text-gray-500 mb-1">Penarikan Pending</div>
            <div class="text-3xl font-bold text-red-600">{{ $pendingWithdrawals }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h2>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Affiliator</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kupon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($recentOrders as $order)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600">#{{ $order->id }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->affiliate->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">
                    @if($order->coupon_code)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $order->coupon_code }}</span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
