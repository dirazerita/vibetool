@extends('layouts.admin')
@section('title', 'Proses Penarikan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Proses Penarikan</h1>

<div class="dk-table">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bank</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $withdrawal)
            <tr>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $withdrawal->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $withdrawal->bank_name }} - {{ $withdrawal->bank_account }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $withdrawal->status === 'approved' ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : ($withdrawal->status === 'pending' ? 'dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"') }}">
                        {{ ucfirst($withdrawal->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $withdrawal->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    @if($withdrawal->status === 'pending')
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium" style="color:#6ee7b7">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" onsubmit="this.querySelector('[name=note]').value = prompt('Alasan penolakan (opsional):') || ''">
                            @csrf
                            <input type="hidden" name="note" value="">
                            <button type="submit" class="text-sm font-medium" style="color:#fca5a5">Tolak</button>
                        </form>
                    </div>
                    @else
                        <span class="text-sm " style="color:#4a5568">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center" style="color:#64748b">Belum ada permintaan penarikan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $withdrawals->links() }}</div>
@endsection
