@extends('layouts.admin')
@section('title', 'Proses Penarikan')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Proses Penarikan</h1>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($withdrawals as $withdrawal)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $withdrawal->user->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $withdrawal->bank_name }} - {{ $withdrawal->bank_account }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $withdrawal->status === 'approved' ? 'bg-green-100 text-green-800' : ($withdrawal->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($withdrawal->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $withdrawal->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4">
                    @if($withdrawal->status === 'pending')
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" onsubmit="this.querySelector('[name=note]').value = prompt('Alasan penolakan (opsional):') || ''">
                            @csrf
                            <input type="hidden" name="note" value="">
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Tolak</button>
                        </form>
                    </div>
                    @else
                        <span class="text-sm text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada permintaan penarikan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $withdrawals->links() }}</div>
@endsection
