@extends('layouts.dashboard')
@section('title', 'Detail Member Tim')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="mb-4">
    <a href="{{ route('dashboard.team') }}" class="text-sm font-medium" style="color:#818cf8">&larr; Kembali ke Tim / Downline</a>
</div>

{{-- Header member --}}
<div class="dk-card" style="padding:24px;margin-bottom:24px">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px">
        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <h1 class="text-2xl font-bold dk-heading">{{ $member->name }}</h1>
                @if($isDirect)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(52,211,153,0.15);color:#6ee7b7">Tim Langsung</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:rgba(252,211,77,0.15);color:#fcd34d">Downline Tim</span>
                @endif
            </div>
            <div class="text-sm dk-text-muted mt-1">{{ $member->email }}</div>
            <div class="text-xs dk-text-muted mt-1">Bergabung {{ $member->created_at->format('d M Y') }}</div>
        </div>
        @if($member->whatsapp_number)
            <a href="https://wa.me/{{ $member->whatsapp_number }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium"
               style="background:#16a34a;color:#fff">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24z"></path></svg>
                Hubungi via WhatsApp
            </a>
        @endif
    </div>
</div>

{{-- Ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="dk-grid-4">
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Penjualan (sbg Affiliator)</div>
        <div style="margin-top:6px;font-size:24px;font-weight:700;color:#6ee7b7">{{ $salesCount }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Omzet Penjualan</div>
        <div style="margin-top:6px;font-size:18px;font-weight:700;color:#6ee7b7">Rp {{ number_format($salesRevenue, 0, ',', '.') }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Produk Dibeli</div>
        <div style="margin-top:6px;font-size:24px;font-weight:700;color:#818cf8">{{ $purchases->count() }}</div>
    </div>
    <div style="background:#1a2332;border-radius:14px;border:1px solid #2d3a4a;padding:20px;text-align:center">
        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Komisi untuk Anda</div>
        <div style="margin-top:6px;font-size:18px;font-weight:700;color:#c4b5fd">Rp {{ number_format($commissionForViewer, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Produk yang dibeli member --}}
<div class="dk-table" style="margin-bottom:24px">
    <div style="padding:16px 24px;border-bottom:1px solid #2d3a4a">
        <h2 class="text-base font-semibold dk-heading">Produk yang Dibeli</h2>
    </div>
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Komisi untuk Anda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $order)
            <tr>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ \Illuminate\Support\Carbon::parse($order->paid_at ?? $order->created_at)->format('d M Y') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $order->product->title ?? 'Produk dihapus' }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm font-medium" style="color:#c4b5fd">
                    @php $oc = optional($commissionByOrder->get($order->id))->sum('amount') ?? 0; @endphp
                    {{ $oc > 0 ? 'Rp ' . number_format($oc, 0, ',', '.') : '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center" style="color:#64748b">Member ini belum membeli produk apa pun. Saatnya follow-up!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Sub-downline --}}
<div class="dk-table">
    <div style="padding:16px 24px;border-bottom:1px solid #2d3a4a">
        <h2 class="text-base font-semibold dk-heading">Downline dari {{ $member->name }} ({{ $subDownlines->count() }})</h2>
    </div>
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bergabung</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Penjualan</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($subDownlines as $sub)
            <tr>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $sub->name }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $sub->email }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $sub->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $sub->total_sales }}</td>
                <td class="px-6 py-4 text-sm">
                    <a href="{{ route('dashboard.team.show', $sub->id) }}" class="text-xs font-medium" style="color:#818cf8">Lihat detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">Belum punya downline.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
