@extends('layouts.admin')
@section('title', 'Lisensi')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-2">Manajemen Lisensi</h1>
<p class="text-sm text-gray-600 mb-6">Daftar produk bertipe <strong>Software / Tool</strong>. Klik produk untuk mengelola kunci lisensinya.</p>

@if($products->total() === 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <p class="text-gray-500 mb-2">Belum ada produk bertipe software.</p>
        <p class="text-xs text-gray-400">Ubah tipe produk menjadi "Software / Tool" di halaman edit produk untuk mengelola lisensinya di sini.</p>
    </div>
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Lisensi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sudah Diberikan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tersedia</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Order Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                    @php
                        $pendingAssign = $product->paid_orders - $product->assigned_licenses;
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                            <div class="text-xs text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ number_format($product->total_licenses) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ number_format($product->assigned_licenses) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $product->available_licenses > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($product->available_licenses) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            {{ number_format($product->paid_orders) }}
                            @if($pendingAssign > 0)
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $pendingAssign }} blm dapat
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.licenses.show', $product) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Kelola</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $products->links() }}</div>
@endif
@endsection
