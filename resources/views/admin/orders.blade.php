@extends('layouts.admin')
@section('title', 'Semua Pesanan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Semua Pesanan</h1>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Affiliator</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($orders as $order)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600">#{{ $order->id }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $order->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->affiliate->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
