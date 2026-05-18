@extends('layouts.admin')
@section('title', 'Komisi Member')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Komisi Member</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Member Ber-komisi</div>
        <div class="text-3xl font-bold text-gray-900">{{ number_format($summary['total_members']) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Total Komisi</div>
        <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Komisi Direct</div>
        <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['total_direct'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm text-gray-500 mb-1">Bonus Upline</div>
        <div class="text-2xl font-bold text-purple-600">Rp {{ number_format($summary['total_upline'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WhatsApp</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Komisi Direct</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bonus Upline</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Komisi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"># Transaksi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($members as $member)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $member->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $member->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $member->whatsapp_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-right text-emerald-700">Rp {{ number_format((float) $member->direct_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right text-purple-700">Rp {{ number_format((float) $member->upline_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-700">Rp {{ number_format((float) $member->total_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-700">{{ $member->commissions_count }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-900">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.commissions.show', $member) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">Belum ada member yang menerima komisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
