@extends('layouts.dashboard')
@section('title', 'Detail Penjualan')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Detail Penjualan</h1>
        <p class="text-sm dk-text-muted mt-1">Rincian dari mana angka <strong>Total Penjualan</strong> di dashboard datang.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="dk-btn dk-btn-secondary text-sm">&larr; Kembali ke Dashboard</a>
</div>

<div class="dk-grid-3 gap-4 mb-6" style="display:grid;grid-template-columns:repeat(3,1fr)">
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Order Penjualan</div>
            <div class="text-2xl font-bold" style="color:#60a5fa">{{ $totalSales }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Omset Penjualan</div>
            <div class="text-2xl font-bold" style="color:#a78bfa">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Komisi Anda</div>
            <div class="text-2xl font-bold" style="color:#6ee7b7">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="dk-card mb-6" style="padding:20px">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="text-lg font-semibold dk-heading">Penjualan dari Downline</h2>
            <p class="text-xs dk-text-muted mt-1">Order paid (bukan produk gratis) di mana Anda adalah <em>affiliate</em> langsung (Level 1) atau <em>upline</em> dari affiliate (Level 2). Inilah yang menghasilkan komisi <strong>Komisi Langsung</strong> &amp; <strong>Bonus Upline</strong>.</p>
        </div>
        <div class="text-right">
            <div class="text-xs dk-text-muted">Order</div>
            <div class="text-xl font-bold" style="color:#60a5fa">{{ $downlineCount }}</div>
            <div class="text-xs dk-text-muted mt-1">Komisi Anda</div>
            <div class="text-sm font-semibold" style="color:#6ee7b7">Rp {{ number_format($downlineCommission, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="dk-table">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembeli</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Sumber</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse($downlineSales as $order)
                <tr>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3" style="color:#e2e8f0">{{ $order->user->name ?? '-' }}</td>
                    <td class="px-4 py-3" style="color:#e2e8f0">{{ $order->product->title ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($order->affiliate_id == $user->id)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">L1 (Direct)</span>
                        @elseif($order->upline_id == $user->id)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(59,130,246,0.15);color:#93c5fd">L2 (Upline)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-medium" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center" style="color:#64748b">Belum ada penjualan dari downline.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $downlineSales->links() }}</div>
</div>

@if($isVendor)
<div class="dk-card" style="padding:20px">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="text-lg font-semibold dk-heading">Penjualan Produk Saya (di luar Downline)</h2>
            <p class="text-xs dk-text-muted mt-1">Order paid produk yang Anda upload sendiri, dibeli oleh orang yang <strong>bukan</strong> downline Anda. Ini menghasilkan komisi <strong>Sebagai Pembuat (creator share)</strong>.</p>
        </div>
        <div class="text-right">
            <div class="text-xs dk-text-muted">Order</div>
            <div class="text-xl font-bold" style="color:#fbbf24">{{ $externalCount }}</div>
            <div class="text-xs dk-text-muted mt-1">Komisi Pembuat</div>
            <div class="text-sm font-semibold" style="color:#6ee7b7">Rp {{ number_format($externalCommission, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="dk-table">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembeli</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk Saya</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Affiliate</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @if($externalSales)
                    @forelse($externalSales as $order)
                    <tr>
                        <td class="px-4 py-3" style="color:#94a3b8">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3" style="color:#e2e8f0">{{ $order->user->name ?? '-' }}</td>
                        <td class="px-4 py-3" style="color:#e2e8f0">{{ $order->product->title ?? '-' }}</td>
                        <td class="px-4 py-3" style="color:#94a3b8">{{ $order->affiliate->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-medium" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center" style="color:#64748b">Belum ada penjualan produk Anda di luar downline.</td>
                    </tr>
                    @endforelse
                @else
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center" style="color:#64748b">Anda belum punya produk yang aktif.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if($externalSales) <div class="mt-3">{{ $externalSales->links() }}</div> @endif
</div>
@endif
@endsection
