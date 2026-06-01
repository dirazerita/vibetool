@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<h1 class="dk-heading" style="font-size:1.5rem; font-weight:700; margin-bottom:24px;">Admin Dashboard</h1>

<div class="dk-grid-3" style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px; margin-bottom:32px;">
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(99,102,241,0.15); color:#818cf8;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Total Member</div>
            <div style="font-size:1.875rem; font-weight:700; color:#818cf8;">{{ $totalMembers }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(168,85,247,0.15); color:#c4b5fd;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Total Produk</div>
            <div style="font-size:1.875rem; font-weight:700; color:#c4b5fd;">{{ $totalProducts }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(16,185,129,0.15); color:#6ee7b7;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Pesanan Sukses</div>
            <div style="font-size:1.875rem; font-weight:700; color:#6ee7b7;">{{ $totalOrders }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(96,165,250,0.15); color:#60a5fa;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Total Revenue</div>
            <div style="font-size:1.875rem; font-weight:700; color:#60a5fa;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(251,146,60,0.15); color:#fdba74;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Total Komisi Dibayar</div>
            <div style="font-size:1.875rem; font-weight:700; color:#fdba74;">Rp {{ number_format($totalCommissions, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(239,68,68,0.15); color:#fca5a5;">
            <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:14px; margin-bottom:4px;">Penarikan Pending</div>
            <div style="font-size:1.875rem; font-weight:700; color:#fca5a5;">{{ $pendingWithdrawals }}</div>
        </div>
    </div>
</div>

<div class="dk-table">
    <div style="padding:16px 24px; border-bottom:1px solid #2d3a4a;">
        <h2 class="dk-heading" style="font-size:1.125rem; font-weight:600;">Pesanan Terbaru</h2>
    </div>
    <table class="min-w-full">
        <thead>
            <tr>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">ID</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Pembeli</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Produk</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Affiliator</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Kupon</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Jumlah</th>
                <th style="color:#94a3b8; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentOrders as $order)
            <tr style="border-bottom:1px solid #1e2b3d;">
                <td style="padding:16px 24px; font-size:14px; color:#94a3b8;">#{{ $order->id }}</td>
                <td style="padding:16px 24px; font-size:14px; color:#e2e8f0;">{{ $order->user->name ?? '-' }}</td>
                <td style="padding:16px 24px; font-size:14px; color:#94a3b8;">{{ $order->product->title ?? '-' }}</td>
                <td style="padding:16px 24px; font-size:14px; color:#94a3b8;">{{ $order->affiliate->name ?? '-' }}</td>
                <td style="padding:16px 24px; font-size:14px;">
                    @if($order->coupon_code)
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:12px;font-family:monospace;font-weight:500;background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3);">{{ $order->coupon_code }}</span>
                    @else
                        <span style="color:#4a5568;">-</span>
                    @endif
                </td>
                <td style="padding:16px 24px; font-size:14px; font-weight:600; color:#e2e8f0;">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td style="padding:16px 24px; font-size:14px;">
                    @if($order->status === 'paid')
                        <span class="dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7;">Paid</span>
                    @elseif($order->status === 'pending')
                        <span class="dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047;">Pending</span>
                    @else
                        <span class="dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5;">{{ ucfirst($order->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding:32px 24px; text-align:center; color:#64748b;">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
