@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Member</div>
        <div class="text-3xl font-bold text-indigo-600">{{ $totalMembers }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Produk</div>
        <div class="text-3xl font-bold text-purple-600">{{ $totalProducts }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Pesanan Sukses</div>
        <div class="text-3xl font-bold text-green-600">{{ $totalOrders }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
        <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Komisi Dibayar</div>
        <div class="text-3xl font-bold text-orange-600">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Penarikan Pending</div>
        <div class="text-3xl font-bold text-red-600">{{ $pendingWithdrawals }}</div>
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
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
