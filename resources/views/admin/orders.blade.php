@extends('layouts.admin')
@section('title', 'Semua Pesanan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Semua Pesanan</h1>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif

<div class="dk-table">
    <div class="overflow-x-auto">
        <table class="min-w-full">
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
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">#{{ $order->id }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">
                    {{ $order->user->name ?? '-' }}
                    @if($order->user && $order->user->status !== 'active')
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium " style="background:rgba(251,146,60,0.15);color:#fdba74;border:1px solid rgba(251,146,60,0.3)">
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Member Belum Aktif
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $order->product->title ?? '-' }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $order->affiliate->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm">
                    @if($order->coupon_code)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium" style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3)">{{ $order->coupon_code }}</span>
                        @if($order->discount_amount)
                            <div class="mt-0.5 text-[11px] dk-text-muted">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</div>
                        @endif
                    @else
                        <span style="color:#4a5568">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4">
                    @if($order->payment_method === 'manual')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium dk-badge" style="background:rgba(168,85,247,0.15);color:#c4b5fd">Manual</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium " style="background:#151e2d dk-text">Xendit</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'paid' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($order->status === 'pending' ? 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $order->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4 text-sm whitespace-nowrap align-top">
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
@endsection
