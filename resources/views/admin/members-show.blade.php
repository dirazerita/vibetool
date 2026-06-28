@extends('layouts.admin')
@section('title', 'Detail Member')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .tree-node {
        display: inline-block; border: 2px solid #34d399; border-radius: 8px;
        padding: 6px 10px; min-width: 100px; max-width: 130px; background: #1a2332;
        text-align: center; font-size: 10px; color: #e2e8f0; text-decoration: none;
    }
    .tree-node:hover { border-color: #818cf8; cursor: pointer; }
    .tree-node.level-2 { border-color: #fcd34d; }
    .tree-node.level-3 { border-color: #818cf8; }
    .tree-line { width: 2px; height: 14px; background: #2d3a4a; margin: 0 auto; }
</style>

<div class="mb-4">
    <a href="{{ route('admin.members') }}" class="text-sm font-medium" style="color:#818cf8">&larr; Kembali ke Daftar Member</a>
</div>

{{-- Header member --}}
<div class="dk-card" style="padding:24px;margin-bottom:24px;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <h1 class="text-2xl font-bold dk-heading">{{ $user->name }}</h1>
                @if($user->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Active</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047">Pending</span>
                @endif
            </div>
            <div class="text-sm dk-text-muted mt-1">{{ $user->email }} | {{ $user->whatsapp_number ?? '—' }}</div>
            <div class="text-xs dk-text-muted mt-1">Bergabung {{ $user->created_at->format('d M Y') }} | Upline: {{ $user->upline->name ?? '—' }}</div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.members.edit', $user) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium" style="background:#4f46e5;color:#fff">Edit</a>
            @if($user->status === 'active')
                <form method="POST" action="{{ route('admin.members.deactivate', $user) }}" class="inline" onsubmit="return confirm('Nonaktifkan {{ $user->name }}?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium" style="background:#d97706;color:#fff">Nonaktifkan</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.members.activate', $user) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium" style="background:#16a34a;color:#fff">Aktifkan</button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Grid: total transaksi, total belanja, total komisi, downline --}}
@php
    $stats = [
        'total_orders' => $user->orders()->count(),
        'total_paid' => $user->orders()->where('status', 'paid')->count(),
        'total_spent' => (float) $user->orders()->where('status', 'paid')->sum('amount'),
        'total_commission' => (float) \App\Models\Commission::where('user_id', $user->id)->sum('amount'),
        'downline_count' => (int) $user->downlines()->count(),
    ];
@endphp
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:24px" class="dk-grid-5">
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase">Semua Pesanan</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#818cf8">{{ $stats['total_orders'] }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase">Lunas</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#6ee7b7">{{ $stats['total_paid'] }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase">Total Belanja (Lunas)</div>
        <div style="margin-top:6px;font-size:20px;font-weight:700;color:#c4b5fd">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase">Komisi Diterima</div>
        <div style="margin-top:6px;font-size:20px;font-weight:700;color:#fbbf24">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase">Downline</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#6ee7b7">{{ $stats['downline_count'] }}</div>
    </div>
</div>

{{-- Riwayat Pembelian --}}
<div class="dk-table" style="margin-bottom:24px">
    <div style="padding:16px 24px;border-bottom:1px solid #2d3a4a">
        <h2 class="text-base font-semibold dk-heading">Riwayat Pembelian</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="px-4 py-3 text-sm" style="color:#e2e8f0">{{ $order->product->title ?? 'Produk dihapus' }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($order->status === 'paid')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Lunas</span>
                        @elseif($order->status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047">Menunggu</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background:rgba(239,68,68,0.15);color:#fca5a5">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-right" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-sm" style="color:#94a3b8">{{ ($order->paid_at ?? $order->created_at)->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($order->status === 'paid')
                        <form method="POST" action="{{ route('admin.orders.reverse-payment', $order) }}" class="inline"
                              onsubmit="return confirm('Batalkan status LUNAS pesanan ini? Komisi & lisensi akan dihapus.')">
                            @csrf
                            <button type="submit" class="text-xs font-medium" style="color:#fca5a5">Batalkan Lunas</button>
                        </form>
                        @elseif($order->status === 'pending')
                        <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-medium" style="color:#6ee7b7">Tandai Lunas</button>
                        </form>
                        @else
                            <span style="color:#4a5568">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center" style="color:#64748b">Belum ada pembelian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mb-6">{{ $orders->links() }}</div>

{{-- Tree Tim --}}
<div class="dk-card" style="padding:24px">
    <h2 class="text-base font-semibold dk-heading mb-4">Tim / Downline</h2>
    @if($member->downlines->isEmpty())
        <p class="text-sm dk-text-muted">Belum punya downline.</p>
    @else
    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;padding-top:12px;">
        {{-- Root node (member ini) --}}
        <div class="tree-node" style="border-color:#818cf8;font-weight:600;padding:8px 14px;min-width:120px;">
            <div>{{ $user->name }}</div>
            <div class="text-[9px] dk-text-muted">{{ $user->email }}</div>
        </div>

        <div style="width:2px;height:16px;background:#2d3a4a;"></div>

        {{-- Level 2 (direct downlines) --}}
        <div style="display:flex;gap:20px;flex-wrap:wrap;justify-content:center;">
            @foreach($member->downlines as $lv2)
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                <a href="{{ route('admin.members.show', $lv2) }}" class="tree-node level-2">{{ $lv2->name }}</a>
                @if($lv2->downlines->isNotEmpty())
                <div style="width:2px;height:12px;background:#2d3a4a;"></div>
                {{-- Level 3 --}}
                <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                    @foreach($lv2->downlines as $lv3)
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <a href="{{ route('admin.members.show', $lv3) }}" class="tree-node level-3">{{ $lv3->name }}</a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection