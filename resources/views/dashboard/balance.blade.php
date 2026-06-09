@extends('layouts.dashboard')
@section('title', 'Detail Saldo')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Detail Saldo</h1>
        <p class="text-sm dk-text-muted mt-1">Rincian dari mana angka <strong>Saldo</strong> di dashboard datang.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="dk-btn dk-btn-secondary text-sm">&larr; Kembali ke Dashboard</a>
</div>

<div class="dk-grid-3 gap-4 mb-6" style="display:grid;grid-template-columns:repeat(3,1fr)">
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Saldo Saat Ini</div>
            <div class="text-2xl font-bold" style="color:#818cf8">Rp {{ number_format($user->balance, 0, ',', '.') }}</div>
            <p class="text-xs dk-text-muted mt-1">Saldo aktif yang siap ditarik.</p>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Komisi Diterima</div>
            <div class="text-2xl font-bold" style="color:#6ee7b7">Rp {{ number_format($totalApprovedCommission, 0, ',', '.') }}</div>
            <p class="text-xs dk-text-muted mt-1">Komisi <em>approved</em> yang sudah masuk saldo.</p>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Pernah Ditarik</div>
            <div class="text-2xl font-bold" style="color:#fbbf24">Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}</div>
            <p class="text-xs dk-text-muted mt-1">Penarikan <em>approved</em> ke rekening Anda.</p>
        </div>
    </div>
</div>

@if($totalPendingCommission > 0 || $totalPendingWithdrawal > 0)
<div class="dk-card mb-6" style="padding:16px;background:rgba(234,179,8,0.06);border:1px solid rgba(234,179,8,0.25)">
    <div class="text-sm" style="color:#fde68a">
        @if($totalPendingCommission > 0)
            <div>Komisi belum ter-approve: <strong>Rp {{ number_format($totalPendingCommission, 0, ',', '.') }}</strong></div>
        @endif
        @if($totalPendingWithdrawal > 0)
            <div>Permintaan penarikan menunggu approval: <strong>Rp {{ number_format($totalPendingWithdrawal, 0, ',', '.') }}</strong></div>
        @endif
    </div>
</div>
@endif

<div class="dk-card mb-6" style="padding:20px">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <h2 class="text-lg font-semibold dk-heading">Pemasukan (Komisi yang masuk saldo)</h2>
        <a href="{{ route('dashboard.commissions') }}" class="text-xs" style="color:#818cf8">Lihat semua komisi &rarr;</a>
    </div>
    <div class="dk-table">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Sumber</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tipe</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Masuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commissions as $c)
                <tr>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $c->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3" style="color:#e2e8f0">{{ $c->order->product->title ?? 'Produk dihapus' }}</td>
                    <td class="px-4 py-3">
                        @if($c->type === 'direct')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Komisi Langsung</span>
                        @elseif($c->type === 'upline')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(59,130,246,0.15);color:#93c5fd">Bonus Upline</span>
                        @elseif($c->type === 'creator')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(251,191,36,0.15);color:#fbbf24">Sebagai Pembuat</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(100,116,139,0.15);color:#cbd5e1">{{ ucfirst($c->type) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold" style="color:#6ee7b7">+ Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center" style="color:#64748b">Belum ada komisi yang masuk saldo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $commissions->links() }}</div>
</div>

<div class="dk-card" style="padding:20px">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <h2 class="text-lg font-semibold dk-heading">Pengeluaran (Penarikan)</h2>
        <a href="{{ route('dashboard.withdrawals') }}" class="text-xs" style="color:#818cf8">Halaman penarikan &rarr;</a>
    </div>
    <div class="dk-table">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tujuan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Keluar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                <tr>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $w->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3" style="color:#e2e8f0">{{ $w->bank_name }} — {{ $w->bank_account }}</td>
                    <td class="px-4 py-3">
                        @if($w->status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Berhasil</span>
                        @elseif($w->status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047">Menunggu</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(239,68,68,0.15);color:#fca5a5">Ditolak</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold" style="color:#fca5a5">- Rp {{ number_format($w->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center" style="color:#64748b">Belum ada penarikan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $withdrawals->links() }}</div>
</div>
@endsection
