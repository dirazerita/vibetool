@extends('layouts.dashboard')
@section('title', 'Riwayat Komisi')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Riwayat Komisi</h1>

<div class="dk-table">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commissions as $commission)
            <tr>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $commission->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $commission->order->product->title ?? '-' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $commission->type === 'direct' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : 'bg-blue-100 text-blue-800' }}">
                        {{ $commission->type === 'direct' ? 'Komisi Langsung' : 'Bonus Upline' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-green-600">Rp {{ number_format($commission->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $commission->status === 'approved' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($commission->status === 'pending' ? 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"' : '" style="background:#151e2d text-gray-800') }}">
                        {{ ucfirst($commission->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">Belum ada komisi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $commissions->links() }}</div>
@endsection
