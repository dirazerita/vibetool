@extends('layouts.admin')
@section('title', 'Komisi Khusus Member')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Komisi Khusus Member</h1>
    <a href="{{ route('admin.member-commissions.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">+ Tambah Komisi Khusus</a>
</div>

<p class="text-sm text-gray-500 mb-4">Atur tarif komisi khusus untuk member tertentu. Komisi khusus akan menggantikan tarif default produk.</p>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Komisi Affiliator (%)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bonus Upline (%)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tarif Default Produk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($memberCommissions as $mc)
                <tr>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium text-gray-900">{{ $mc->user->name }}</div>
                        <div class="text-gray-500 text-xs">{{ $mc->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $mc->product->title }}</td>
                    <td class="px-6 py-4 text-sm text-right">
                        @if($mc->commission_percent !== null)
                            <span class="font-semibold text-indigo-700">{{ $mc->commission_percent }}%</span>
                        @else
                            <span class="text-gray-400">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right">
                        @if($mc->upline_percent !== null)
                            <span class="font-semibold text-purple-700">{{ $mc->upline_percent }}%</span>
                        @else
                            <span class="text-gray-400">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-right text-gray-500">
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
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada komisi khusus. Klik tombol "+ Tambah Komisi Khusus" untuk menambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $memberCommissions->links() }}</div>
@endsection
