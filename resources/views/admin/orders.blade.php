@extends('layouts.admin')
@section('title', 'Semua Pesanan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Semua Pesanan</h1>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Affiliator</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kupon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($orders as $order)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600">#{{ $order->id }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    {{ $order->user->name ?? '-' }}
                    @if($order->user && $order->user->status !== 'active')
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-orange-100 text-orange-800 border border-orange-200">
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Member Belum Aktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->affiliate->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">
                    @if($order->coupon_code)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $order->coupon_code }}</span>
                        @if($order->discount_amount)
                            <div class="mt-0.5 text-[11px] text-gray-500">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</div>
                        @endif
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    @if($order->payment_method === 'manual')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Manual</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Xendit</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4 text-sm whitespace-nowrap align-top">
                    @if($order->status === 'pending' && $order->payment_method === 'manual')
                        @php
                            $memberInactive = $order->user && $order->user->status !== 'active';
                            $hasProof = !empty($order->payment_proof);
                            $confirmMessage = $memberInactive
                                ? 'Tandai pesanan #' . $order->id . ' sebagai lunas? Member "' . $order->user->name . '" sekaligus akan diaktifkan dan komisi affiliator & upline akan dicairkan.'
                                : 'Tandai pesanan #' . $order->id . ' sebagai lunas? Komisi affiliator & upline akan dicairkan.';
                        @endphp
                        <div class="flex flex-col items-start gap-1.5">
                            @if($hasProof)
                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Lihat Bukti</a>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Belum upload bukti
                                </span>
                            @endif
                            <form method="POST" action="{{ route('admin.orders.mark-paid', $order->id) }}" onsubmit="return confirm('{{ $confirmMessage }}');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium shadow-sm"
                                        style="background-color:#16a34a; color:#ffffff;"
                                        onmouseover="this.style.backgroundColor='#15803d'"
                                        onmouseout="this.style.backgroundColor='#16a34a'"
                                        title="{{ $memberInactive ? 'Member belum aktif — akan diaktifkan otomatis saat ditandai lunas.' : 'Tandai pesanan ini sebagai lunas.' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $memberInactive ? 'Aktifkan & Tandai Lunas' : 'Tandai Lunas' }}</span>
                                </button>
                            </form>
                        </div>
                    @elseif($order->status === 'paid' && $order->payment_method === 'manual' && $order->payment_proof)
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Lihat Bukti</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
