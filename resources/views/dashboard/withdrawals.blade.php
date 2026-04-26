@extends('layouts.dashboard')
@section('title', 'Penarikan Saldo')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Penarikan Saldo</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Ajukan Penarikan</h2>
            <div class="mb-4">
                <div class="text-sm text-gray-500">Saldo Tersedia</div>
                <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            </div>
            <form method="POST" action="{{ route('dashboard.withdrawals.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Penarikan</label>
                    <input type="number" name="amount" id="amount" min="50000" step="1000" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Min. Rp 50.000" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Ajukan Penarikan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Penarikan</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $withdrawal->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $withdrawal->bank_name }} - {{ $withdrawal->bank_account }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $withdrawal->status === 'approved' ? 'bg-green-100 text-green-800' : ($withdrawal->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                            @if($withdrawal->note)
                                <p class="text-xs text-gray-500 mt-1">{{ $withdrawal->note }}</p>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat penarikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $withdrawals->links() }}</div>
    </div>
</div>
@endsection
