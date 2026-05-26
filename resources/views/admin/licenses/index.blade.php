@extends('layouts.admin')
@section('title', 'Lisensi')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-2">Manajemen Lisensi</h1>
<p class="text-sm dk-text mb-6">Daftar produk bertipe <strong>Software / Tool</strong>. Klik produk untuk mengelola kunci lisensinya.</p>

@if($products->total() === 0)
    <div class="dk-card p-10 text-center">
        <p class="dk-text-muted mb-2">Belum ada produk bertipe software.</p>
        <p class="text-xs " style="color:#4a5568">Ubah tipe produk menjadi "Software / Tool" di halaman edit produk untuk mengelola lisensinya di sini.</p>
    </div>
@else
<div class="dk-table">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Total Lisensi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Sudah Diberikan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Tersedia</th>
                    <th class="px-6 py-3 text-right text-xs font-medium dk-text-muted uppercase">Order Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody class="dk-card ">
                @foreach($products as $product)
                    @php
                        $pendingAssign = $product->paid_orders - $product->assigned_licenses;
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium dk-heading">{{ $product->title }}</div>
                            <div class="dk-text-muted" style="font-size:12px">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm dk-heading">{{ number_format($product->total_licenses) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm dk-heading">{{ number_format($product->assigned_licenses) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $product->available_licenses > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ number_format($product->available_licenses) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            {{ number_format($product->paid_orders) }}
                            @if($pendingAssign > 0)
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium dk-badge" style="background:rgba(234,179,8,0.15);color:#fde047">
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
