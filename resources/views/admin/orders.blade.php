@extends('layouts.admin')
@section('title', 'Semua Pesanan')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    @media (max-width: 768px) {
        /* Ubah tabel pesanan jadi kartu bertumpuk di layar kecil */
        .orders-table-wrap { background: transparent !important; border: none !important; border-radius: 0 !important; overflow: visible !important; }
        .orders-table-wrap .overflow-x-auto { overflow: visible !important; }
        .orders-card-table { min-width: 0 !important; width: 100% !important; }
        .orders-card-table thead { display: none; }
        .orders-card-table tbody, .orders-card-table tr, .orders-card-table td { display: block; width: 100%; }
        .orders-card-table tr { background: #1a2332; border: 1px solid #2d3a4a; border-radius: 12px; margin-bottom: 14px; padding: 6px 0; }
        .orders-card-table td {
            display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
            padding: 8px 16px !important; border: none !important; text-align: right; white-space: normal !important;
        }
        .orders-card-table td::before {
            content: attr(data-label); font-weight: 600; color: #94a3b8; text-align: left;
            flex-shrink: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em;
        }
        .orders-card-table td > * { margin-left: auto; }
    }
</style>
<h1 class="text-2xl font-bold dk-heading mb-6">Semua Pesanan</h1>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif
@if(session('warning'))
    <div class="dk-alert-error" style="background:rgba(234,179,8,0.12);border-color:rgba(234,179,8,0.35);color:#fde047">{{ session('warning') }}</div>
@endif

<div class="flex flex-wrap items-center gap-2 mb-4">
    <a href="{{ route('admin.orders') }}"
       class="px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ empty($filter) ? 'background:#4f46e5;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Semua Pesanan
    </a>
    <a href="{{ route('admin.orders', ['filter' => 'needs_attribution']) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium"
       style="{{ $filter === 'needs_attribution' ? 'background:#b45309;color:#fff' : 'background:#1a2332;color:#94a3b8;border:1px solid #2d3a4a' }}">
        Perlu Koreksi Komisi
        @if($needsAttributionCount > 0)
            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-semibold" style="background:rgba(234,179,8,0.25);color:#fde047">{{ $needsAttributionCount }}</span>
        @endif
    </a>
</div>

@if($filter === 'needs_attribution')
    <div class="mb-4 text-xs dk-text-muted" style="background:#1a2332;border:1px solid #2d3a4a;border-radius:10px;padding:10px 14px">
        Menampilkan pesanan lunas yang memakai kupon tetapi belum punya affiliator. Klik <strong style="color:#fde047">Tetapkan pemilik kupon</strong> untuk mencairkan komisi pemilik kupon &amp; bonus upline-nya.
    </div>
@endif

<div class="dk-table orders-table-wrap">
    <div class="overflow-x-auto">
        <table class="min-w-full orders-card-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Pembeli</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Affiliator</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Kupon</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Metode</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td class="px-6 py-4 text-sm" data-label="ID" style="color:#94a3b8">#{{ $order->id }}</td>
                <td class="px-6 py-4 text-sm" data-label="Pembeli" style="color:#e2e8f0">
                    {{ $order->user->name ?? '-' }}
                    @if($order->user && $order->user->status !== 'active')
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium " style="background:rgba(251,146,60,0.15);color:#fdba74;border:1px solid rgba(251,146,60,0.3)">
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Member Belum Aktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" data-label="Produk" style="color:#94a3b8">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm" data-label="Affiliator" style="color:#94a3b8">
                    @php $suggestedOwner = $couponSuggestions[$order->id] ?? null; @endphp
                    <div x-data="affiliatePicker({
                            orderId: {{ $order->id }},
                            current: {{ $order->affiliate_id ?? 'null' }},
                            searchUrl: '{{ route('admin.orders.members.search') }}',
                            excludeId: {{ $order->user_id }}
                         })" class="flex flex-col items-end md:items-start gap-1">
                        <div class="flex items-center gap-2">
                            <span style="color:#e2e8f0">{{ $order->affiliate->name ?? '—' }}</span>
                            <button type="button" @click="openModal()" class="text-xs font-medium" style="color:#818cf8" title="Ubah affiliator">Ubah</button>
                        </div>
                        @if($order->upline_id)
                            <div class="text-[11px] dk-text-muted">Upline: {{ $order->uplineUser->name ?? '-' }}</div>
                        @endif

                        @if($suggestedOwner)
                            <form method="POST" action="{{ route('admin.orders.assign-coupon-owner', $order->id) }}" class="mt-1"
                                  onsubmit="return confirm('Tetapkan {{ addslashes($suggestedOwner->name) }} (pemilik kupon {{ $order->coupon_code }}) sebagai affiliator pesanan #{{ $order->id }}? Komisi & bonus upline akan dicairkan.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium"
                                        style="background:rgba(234,179,8,0.15);color:#fde047;border:1px solid rgba(234,179,8,0.35)"
                                        title="Pemilik kupon {{ $order->coupon_code }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Tetapkan pemilik kupon: {{ $suggestedOwner->name }}
                                </button>
                            </form>
                        @endif

                        <div x-show="open" x-cloak @keydown.escape.window="open = false"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4"
                             style="background:rgba(0,0,0,0.6)">
                            <div @click.outside="open = false" class="w-full max-w-md rounded-xl p-5" style="background:#1a2332;border:1px solid #2d3a4a">
                                <h3 class="text-base font-semibold mb-1 dk-heading">Ubah Affiliator — Pesanan #{{ $order->id }}</h3>
                                <p class="text-xs dk-text-muted mb-3">Upline akan otomatis di-set dari upline affiliator yang dipilih. @if($order->status === 'paid')Komisi akan dihitung ulang.@endif</p>
                                <form method="POST" action="{{ route('admin.orders.update-affiliate', $order->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" x-model="query" @input.debounce.300ms="search()" placeholder="Cari nama / email member..."
                                           class="w-full mb-2 px-3 py-2 rounded-md text-sm" style="background:#0f1623;border:1px solid #2d3a4a;color:#e2e8f0">
                                    <select name="affiliate_id" size="8" class="w-full mb-2 rounded-md text-sm" style="background:#0f1623;border:1px solid #2d3a4a;color:#e2e8f0">
                                        <option value="">— Tanpa affiliator —</option>
                                        <template x-for="m in members" :key="m.id">
                                            <option :value="m.id" :selected="m.id === current" x-text="m.name + ' (' + m.email + ')'"></option>
                                        </template>
                                    </select>
                                    <p class="text-[11px] dk-text-muted mb-3" x-show="loading">Memuat…</p>
                                    <p class="text-[11px] dk-text-muted mb-3" x-show="!loading && members.length === 0">Tidak ada member yang cocok.</p>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="open = false" class="px-3 py-1.5 rounded-md text-xs font-medium" style="background:#2d3a4a;color:#cbd5e1">Batal</button>
                                        <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-medium" style="background:#16a34a;color:#fff">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm" data-label="Kupon">
                    @if($order->coupon_code)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium" style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3)">{{ $order->coupon_code }}</span>
                        @if($order->discount_amount)
                            <div class="mt-0.5 text-[11px] dk-text-muted">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</div>
                        @endif
                    @else
                        <span style="color:#4a5568">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-medium" data-label="Jumlah" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4" data-label="Metode">
                    @if($order->payment_method === 'manual')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium dk-badge" style="background:rgba(168,85,247,0.15);color:#c4b5fd">Manual</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium " style="background:#151e2d dk-text">Xendit</span>
                    @endif
                </td>
                <td class="px-6 py-4" data-label="Status">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'paid' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($order->status === 'pending' ? 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"') }}>
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm" data-label="Tanggal" style="color:#94a3b8">{{ $order->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4 text-sm whitespace-nowrap align-top" data-label="Aksi">
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
                                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-xs font-medium" style="color:#818cf8">Lihat Bukti</a>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium " style="background:rgba(234,179,8,0.15);color:#fde047;border:1px solid rgba(234,179,8,0.3)">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Belum upload bukti
                                </span>
                            @endif
                            <form method="POST" action="{{ route('admin.orders.mark-paid', $order->id) }}" onsubmit="return confirm('{{ $confirmMessage }}');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium shadow-sm"
                                        class="dk-btn dk-btn-success" style="
                                        onmouseover="this.style.backgroundColor='#15803d'"
                                        onmouseout="this.style.backgroundColor='#16a34a'"
                                        title="{{ $memberInactive ? 'Member belum aktif — akan diaktifkan otomatis saat ditandai lunas.' : 'Tandai pesanan ini sebagai lunas.' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $memberInactive ? 'Aktifkan & Tandai Lunas' : 'Tandai Lunas' }}</span>
                                </button>
                            </form>
                        </div>
                    @elseif($order->status === 'paid' && $order->payment_method === 'manual' && $order->payment_proof)
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="text-xs font-medium" style="color:#818cf8">Lihat Bukti</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-8 text-center" style="color:#64748b">Belum ada pesanan.</td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $orders->links() }}</div>

<script>
    // Komponen Alpine untuk dropdown affiliator yang dimuat lazy (search).
    // Didefinisikan sebagai fungsi global agar bisa dipakai langsung di x-data.
    // Script inline ini jalan saat parsing, sebelum modul Alpine (deferred) start.
    function affiliatePicker(config) {
        return {
            open: false,
            query: '',
            members: [],
            loading: false,
            current: config.current,
            searchUrl: config.searchUrl,
            excludeId: config.excludeId,
            openModal() {
                this.open = true;
                if (this.members.length === 0) {
                    this.search();
                }
            },
            async search() {
                this.loading = true;
                try {
                    const url = new URL(this.searchUrl, window.location.origin);
                    url.searchParams.set('q', this.query);
                    url.searchParams.set('exclude', this.excludeId);
                    const res = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                    });
                    this.members = res.ok ? await res.json() : [];
                } catch (e) {
                    this.members = [];
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endsection
