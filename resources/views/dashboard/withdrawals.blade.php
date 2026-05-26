@extends('layouts.dashboard')
@section('title', 'Penarikan Saldo')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Penarikan Saldo</h1>

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
                    <input type="number" name="amount" id="amount" min="50000" step="1000" class="w-full dk-input" placeholder="Min. Rp 50.000" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Ajukan Penarikan</button>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center" style="color:#64748b">Belum ada riwayat penarikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $withdrawals->links() }}</div>
    </div>
</div>
@endsection
