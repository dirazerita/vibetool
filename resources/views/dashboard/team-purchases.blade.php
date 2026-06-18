@extends('layouts.dashboard')
@section('title', 'Pembelian Tim')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    @media (max-width: 768px) {
        .tp-table-wrap { background: transparent !important; border: none !important; }
        .tp-card-table thead { display: none; }
        .tp-card-table tbody, .tp-card-table tr, .tp-card-table td { display: block; width: 100%; }
        .tp-card-table tr { background: #1a2332; border: 1px solid #2d3a4a; border-radius: 12px; margin-bottom: 14px; padding: 6px 0; }
        .tp-card-table td {
            display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
            padding: 8px 16px !important; border: none !important; text-align: right; white-space: normal !important;
        }
        .tp-card-table td::before {
            content: attr(data-label); font-weight: 600; color: #94a3b8; text-align: left;
            flex-shrink: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em;
        }
    }
</style>

<h1 class="text-2xl font-bold dk-heading mb-2">Pembelian Tim</h1>
<p class="text-sm dk-text-muted mb-6">Lihat downline mana yang sudah membeli, produk yang mereka beli, dan komisi yang Anda hasilkan. Downline yang belum membeli bisa Anda follow-up.</p>

{{-- Ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="dk-grid-4">
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Total Downline</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#818cf8">{{ $totalDownlines }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Sudah Membeli</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#6ee7b7">{{ $buyerCount }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Belum Membeli</div>
        <div style="margin-top:6px;font-size:26px;font-weight:700;color:#fca5a5">{{ $nonBuyerCount }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Komisi dari Tim</div>
        <div style="margin-top:6px;font-size:22px;font-weight:700;color:#c4b5fd">Rp {{ number_format($teamCommission, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Filter --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <a href="{{ route('dashboard.team-purchases') }}"
       class="px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ empty($filter) ? 'background:#4f46e5;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Semua ({{ $totalDownlines }})
    </a>
    <a href="{{ route('dashboard.team-purchases', ['filter' => 'buyers']) }}"
       class="px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ $filter === 'buyers' ? 'background:#047857;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Sudah Membeli ({{ $buyerCount }})
    </a>
    <a href="{{ route('dashboard.team-purchases', ['filter' => 'non_buyers']) }}"
       class="px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ $filter === 'non_buyers' ? 'background:#b45309;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Perlu Follow-up ({{ $nonBuyerCount }})
    </a>
</div>

<div class="dk-table tp-table-wrap">
    <div class="overflow-x-auto">
        <table class="min-w-full tp-card-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Downline</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah Beli</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Total Belanja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Komisi Saya</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembelian Terakhir</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($downlines as $member)
                <tr x-data="{ open: false }">
                    <td class="px-6 py-4 text-sm" data-label="Downline" style="color:#e2e8f0">
                        <div style="font-weight:600">{{ $member->name }}</div>
                        <div class="text-[11px] dk-text-muted">{{ $member->email }}</div>
                        @if($member->whatsapp_number)
                            <a href="https://wa.me/{{ $member->whatsapp_number }}" target="_blank" class="text-[11px] font-medium" style="color:#34d399">WhatsApp</a>
                        @endif
                    </td>
                    <td class="px-6 py-4" data-label="Status">
                        @if($member->purchase_count > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Sudah membeli</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047">Belum — follow-up</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm" data-label="Jumlah Beli" style="color:#e2e8f0">{{ $member->purchase_count }}</td>
                    <td class="px-6 py-4 text-sm" data-label="Total Belanja" style="color:#e2e8f0">Rp {{ number_format($member->total_spent, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm font-medium" data-label="Komisi Saya" style="color:#c4b5fd">Rp {{ number_format($member->commission_earned, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm" data-label="Pembelian Terakhir" style="color:#94a3b8">
                        {{ $member->last_purchase_at ? \Illuminate\Support\Carbon::parse($member->last_purchase_at)->format('d M Y') : '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm" data-label="Produk">
                        @if($member->purchase_count > 0)
                            <button type="button" @click="open = !open" class="text-xs font-medium" style="color:#818cf8" x-text="open ? 'Sembunyikan' : 'Lihat ' + {{ $member->purchase_count }} + ' produk'"></button>
                            <div x-show="open" x-cloak class="mt-2 space-y-1" style="min-width:200px">
                                @foreach($member->purchased_products as $p)
                                    <div class="text-[11px]" style="background:#0f1623;border:1px solid #2d3a4a;border-radius:8px;padding:6px 8px">
                                        <div style="color:#e2e8f0;font-weight:500">{{ $p['title'] }}</div>
                                        <div class="dk-text-muted" style="display:flex;justify-content:space-between;gap:8px">
                                            <span>{{ \Illuminate\Support\Carbon::parse($p['date'])->format('d M Y') }}</span>
                                            <span>Rp {{ number_format($p['amount'], 0, ',', '.') }}</span>
                                        </div>
                                        @if($p['my_commission'] > 0)
                                            <div style="color:#c4b5fd">Komisi: Rp {{ number_format($p['my_commission'], 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#4a5568">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center" style="color:#64748b">
                        @if($filter === 'buyers')
                            Belum ada downline yang membeli.
                        @elseif($filter === 'non_buyers')
                            Semua downline Anda sudah membeli. Mantap!
                        @else
                            Belum ada downline. Bagikan link referral Anda untuk merekrut tim.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $downlines->links() }}</div>
@endsection
