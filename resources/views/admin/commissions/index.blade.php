@extends('layouts.admin')
@section('title', 'Komisi Member')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Komisi Member</h1>

<div class="gap-6 mb-8" style="display:grid;grid-template-columns:repeat(4,1fr)">
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl " style="background:#151e2d dk-text flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Member Ber-komisi</div>
            <div class="text-3xl font-bold dk-heading">{{ number_format($summary['total_members']) }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(99,102,241,0.15);color:#818cf8;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Total Komisi</div>
            <div class="text-2xl font-bold" style="color:#818cf8">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Komisi Direct</div>
            <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['total_direct'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl" style="background:rgba(168,85,247,0.15);color:#c4b5fd;display:flex;align-items:center;justify-content:center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
        </div>
        <div class="min-w-0">
            <div class="text-sm dk-text-muted mb-1">Bonus Upline</div>
            <div class="text-2xl font-bold" style="color:#c4b5fd">Rp {{ number_format($summary['total_upline'], 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="dk-table">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">WhatsApp</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Komisi Direct</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Bonus Upline</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Total Komisi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase"># Transaksi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Saldo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium" style="color:#e2e8f0">{{ $member->name }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->email }}</td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $member->whatsapp_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-right text-emerald-700">Rp {{ number_format((float) $member->direct_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right text-purple-700">Rp {{ number_format((float) $member->upline_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold text-indigo-700">Rp {{ number_format((float) $member->total_commission, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm text-right dk-text">{{ $member->commissions_count }}</td>
                    <td class="px-6 py-4 text-sm text-right dk-heading">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.commissions.show', $member) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center" style="color:#64748b">Belum ada member yang menerima komisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
