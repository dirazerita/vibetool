@extends('layouts.dashboard')
@section('title', 'Tim / Downline')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Tim / Downline</h1>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Penjualan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($downlines as $downline)
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $downline->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $downline->email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $downline->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4 text-sm font-medium text-indigo-600">{{ $downline->total_sales }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada downline. Bagikan link referral Anda untuk merekrut member baru!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $downlines->links() }}</div>
@endsection
