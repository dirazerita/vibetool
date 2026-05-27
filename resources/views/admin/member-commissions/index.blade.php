@extends('layouts.admin')
@section('title', 'Komisi Khusus Member')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold dk-heading">Komisi Khusus Member</h1>
    <a href="{{ route('admin.member-commissions.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">+ Tambah Komisi Khusus</a>
</div>

<p class="text-sm dk-text-muted mb-4">Atur tarif komisi khusus untuk member tertentu. Komisi khusus akan menggantikan tarif default produk.</p>

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
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Komisi Affiliator (%)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Bonus Upline (%)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Tarif Default Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($memberCommissions as $mc)
                <tr>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium dk-heading">{{ $mc->user->name }}</div>
                        <div class="dk-text-muted text-xs">{{ $mc->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm" style="color:#94a3b8">{{ $mc->product->title }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        @if($mc->commission_percent !== null)
                            <span class="font-semibold text-indigo-700">{{ $mc->commission_percent }}%</span>
                        @else
                            <span style="color:#4a5568">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right">
                        @if($mc->upline_percent !== null)
                            <span class="font-semibold text-purple-700">{{ $mc->upline_percent }}%</span>
                        @else
                            <span style="color:#4a5568">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-right dk-text-muted">
                        Affiliator: {{ $mc->product->commission_percent }}% / {{ $mc->product->commission_percent_non_owner ?? $mc->product->commission_percent }}%<br>
                        Upline: {{ $mc->product->upline_percent }}% / {{ $mc->product->upline_percent_non_owner ?? $mc->product->upline_percent }}%
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.member-commissions.edit', $mc) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.member-commissions.destroy', $mc) }}" onsubmit="return confirm('Hapus komisi khusus ini? Member akan kembali menggunakan tarif default produk.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center" style="color:#64748b">Belum ada komisi khusus. Klik tombol "+ Tambah Komisi Khusus" untuk menambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $memberCommissions->links() }}</div>
@endsection
