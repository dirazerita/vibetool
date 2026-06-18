@extends('layouts.dashboard')
@section('title', 'Penarikan Saldo')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Penarikan Saldo</h1>

@if(!auth()->user()->email_verified_at)
<div class="dk-card mb-6" style="padding:16px 20px; border-left:4px solid #eab308; background:rgba(234,179,8,0.08);">
    <div style="display:flex; gap:12px; align-items:flex-start;">
        <svg style="width:24px; height:24px; color:#eab308; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div style="flex:1;">
            <p class="text-sm font-semibold mb-1" style="color:#fde047;">Email belum terverifikasi</p>
            <p class="text-sm dk-text-muted" style="margin-bottom:8px;">Untuk keamanan, penarikan komisi hanya bisa dilakukan setelah email Anda terverifikasi.</p>
            <a href="{{ route('dashboard.email-verification') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-yellow-500 text-slate-900 rounded-lg hover:bg-yellow-400 font-medium text-sm">
                Verifikasi Email Sekarang
            </a>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="dk-card" style="padding:24px;">
            <h2 class="text-lg font-semibold dk-heading mb-4">Ajukan Penarikan</h2>
            <div class="mb-4">
                <div class="dk-text-muted" style="font-size:14px">Saldo Tersedia</div>
                <div class="text-2xl font-bold" style="color:#818cf8">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            </div>
            <form method="POST" action="{{ route('dashboard.withdrawals.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="dk-label">Jumlah Penarikan</label>
                    <input type="number" name="amount" id="amount" min="50000" step="1000" class="w-full dk-input" placeholder="Min. Rp 50.000" {{ auth()->user()->email_verified_at ? 'required' : 'disabled' }}>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if(auth()->user()->email_verified_at)
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Ajukan Penarikan</button>
                @else
                    <button type="button" disabled class="w-full bg-slate-600 text-slate-400 py-2.5 rounded-lg font-medium cursor-not-allowed" title="Verifikasi email dulu">Verifikasi Email Dulu</button>
                @endif
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="dk-table">
            <div class="px-6 py-4 " style="border-bottom:1px solid #1e2b3d">
                <h2 class="text-lg font-semibold dk-heading">Riwayat Penarikan</h2>
            </div>
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bukti Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $withdrawal->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $withdrawal->bank_name }} - {{ $withdrawal->bank_account }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $withdrawal->status === 'approved' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($withdrawal->status === 'pending' ? 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"') }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                            @if($withdrawal->note)
                                <p class="text-xs mt-1 dk-text-muted">{{ $withdrawal->note }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($withdrawal->hasTransferProof())
                                <a href="{{ $withdrawal->transferProofUrl() }}" target="_blank" class="inline-flex items-center gap-1 font-medium" style="color:#6ee7b7" title="Lihat bukti transfer dari admin">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Lihat Bukti
                                </a>
                            @elseif($withdrawal->status === 'approved')
                                <span class="text-xs dk-text-muted">Menunggu bukti dari admin</span>
                            @else
                                <span style="color:#4a5568">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center" style="color:#64748b">Belum ada riwayat penarikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $withdrawals->links() }}</div>
    </div>
</div>
@endsection
