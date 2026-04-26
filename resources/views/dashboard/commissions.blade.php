@extends('layouts.dashboard')
@section('title', 'Riwayat Komisi')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Komisi</h1>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($commissions as $commission)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $commission->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $commission->order->product->title ?? '-' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $commission->type === 'direct' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $commission->type === 'direct' ? 'Komisi Langsung' : 'Bonus Upline' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-green-600">Rp {{ number_format($commission->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $commission->status === 'approved' ? 'bg-green-100 text-green-800' : ($commission->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($commission->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada komisi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $commissions->links() }}</div>
@endsection
