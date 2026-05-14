@extends('layouts.admin')
@section('title', 'Detail Komisi - ' . $user->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('admin.commissions') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali ke daftar komisi</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Komisi: {{ $user->name }}</h1>
        <p class="text-sm text-gray-500">{{ $user->email }} &middot; {{ $user->whatsapp_number ?? '-' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Total Komisi</div>
        <div class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($stats['total'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Komisi Direct</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['direct'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Bonus Upline</div>
        <div class="text-2xl font-bold text-purple-600 mt-1">Rp {{ number_format($stats['upline'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Jumlah Transaksi</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['count']) }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembeli</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Nominal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($commissions as $commission)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $commission->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">#{{ $commission->order_id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $commission->order->product->title ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $commission->order->user->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($commission->type === 'direct')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Direct</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Upline</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-700">Rp {{ number_format((float) $commission->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($commission->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada komisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $commissions->links() }}</div>
@endsection
