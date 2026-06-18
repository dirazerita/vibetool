@extends('layouts.admin')
@section('title', 'Detail Komisi - ' . $user->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('admin.commissions') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali ke daftar komisi</a>
        <h1 class="text-2xl font-bold dk-heading mt-2">Komisi: {{ $user->name }}</h1>
        <p class="dk-text-muted" style="font-size:14px">{{ $user->email }} &middot; {{ $user->whatsapp_number ?? '-' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Total Komisi</div>
        <div class="text-2xl font-bold" style="color:#818cf8 mt-1">Rp {{ number_format($stats['total'], 0, ',', '.') }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Komisi Direct</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['direct'], 0, ',', '.') }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Bonus Upline</div>
        <div class="text-2xl font-bold" style="color:#c4b5fd mt-1">Rp {{ number_format($stats['upline'], 0, ',', '.') }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Bagian Pembuat</div>
        <div class="text-2xl font-bold mt-1" style="color:#fbbf24">Rp {{ number_format($stats['creator'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Dibayarkan</div>
        <div class="text-2xl font-bold mt-1" style="color:#6ee7b7">Rp {{ number_format($stats['paid_out'] ?? 0, 0, ',', '.') }}</div>
        @if(($stats['pending_payout'] ?? 0) > 0)
            <div class="text-[11px] dk-text-muted mt-0.5">Menunggu: Rp {{ number_format($stats['pending_payout'], 0, ',', '.') }}</div>
        @endif
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Jumlah Transaksi</div>
        <div class="text-2xl font-bold dk-heading mt-1">{{ number_format($stats['count']) }}</div>
    </div>
</div>

{{-- Riwayat pembayaran komisi (penarikan) ke member ini --}}
<div class="dk-table mb-8">
    <div style="padding:16px 24px;border-bottom:1px solid #2d3a4a">
        <h2 class="text-base font-semibold dk-heading">Riwayat Pembayaran Komisi</h2>
        <p class="text-xs dk-text-muted mt-0.5">Pencairan komisi ke rekening member via penarikan. "Disetujui" = sudah dibayarkan.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Nominal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Rekening Tujuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                <tr>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $payout->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold" style="color:#6ee7b7">Rp {{ number_format((float) $payout->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $payout->bank_name }} &middot; {{ $payout->bank_account }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($payout->status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Disetujui (Dibayarkan)</span>
                        @elseif($payout->status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047">Menunggu</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5">Ditolak</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $payout->note ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">Belum ada pembayaran/penarikan komisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="dk-table">
    <div style="padding:16px 24px;border-bottom:1px solid #2d3a4a">
        <h2 class="text-base font-semibold dk-heading">Rincian Komisi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembeli</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tipe</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Nominal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $commission)
                <tr>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $commission->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">#{{ $commission->order_id }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $commission->order->product->title ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $commission->order->user->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($commission->type === 'direct')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Direct</span>
                        @elseif($commission->type === 'upline')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium dk-badge" style="background:rgba(168,85,247,0.15);color:#c4b5fd">Upline</span>
                        @elseif($commission->type === 'creator')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(251,191,36,0.15);color:#fbbf24">Pembuat</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(100,116,139,0.15);color:#cbd5e1">{{ ucfirst($commission->type) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-700">Rp {{ number_format((float) $commission->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $commission->status === 'paid' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"') }}">
                            {{ ucfirst($commission->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center" style="color:#64748b">Tidak ada komisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $commissions->links() }}</div>
@endsection
