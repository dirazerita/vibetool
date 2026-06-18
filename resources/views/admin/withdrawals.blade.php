@extends('layouts.admin')
@section('title', 'Proses Penarikan')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Proses Penarikan</h1>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="dk-alert-error">{{ $errors->first() }}</div>
@endif

<div class="dk-table">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bank</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Bukti Transfer</th>
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
                <td class="px-6 py-4 text-sm">
                    @if($withdrawal->hasTransferProof())
                        <a href="{{ $withdrawal->transferProofUrl() }}" target="_blank" class="inline-flex items-center gap-1 font-medium" style="color:#6ee7b7">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Lihat
                        </a>
                    @else
                        <span style="color:#4a5568">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $withdrawal->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    @if($withdrawal->status === 'pending')
                    <div class="flex flex-col gap-2" style="min-width:220px">
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" enctype="multipart/form-data" class="flex flex-col gap-1.5">
                            @csrf
                            <input type="file" name="transfer_proof" accept="image/*" class="text-xs" style="color:#94a3b8">
                            <button type="submit" class="text-sm font-medium text-left" style="color:#6ee7b7">Setujui + simpan bukti</button>
                        </form>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" onsubmit="this.querySelector('[name=note]').value = prompt('Alasan penolakan (opsional):') || ''">
                            @csrf
                            <input type="hidden" name="note" value="">
                            <button type="submit" class="text-sm font-medium text-left" style="color:#fca5a5">Tolak</button>
                        </form>
                    </div>
                    @elseif($withdrawal->status === 'approved')
                        <form method="POST" action="{{ route('admin.withdrawals.upload-proof', $withdrawal) }}" enctype="multipart/form-data" class="flex flex-col gap-1.5" style="min-width:200px">
                            @csrf
                            <input type="file" name="transfer_proof" accept="image/*" class="text-xs" style="color:#94a3b8" required>
                            <button type="submit" class="text-sm font-medium text-left" style="color:#818cf8">{{ $withdrawal->hasTransferProof() ? 'Ganti bukti transfer' : 'Upload bukti transfer' }}</button>
                        </form>
                    @else
                        <span class="text-sm " style="color:#4a5568">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center" style="color:#64748b">Belum ada permintaan penarikan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $withdrawals->links() }}</div>
@endsection
