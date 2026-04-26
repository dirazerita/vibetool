@extends('layouts.admin')
@section('title', 'Kelola Kupon')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kelola Kupon</h1>
    <a href="{{ route('admin.coupons.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Tambah Kupon</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diskon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assign Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assign Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expired</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($coupons as $coupon)
            <tr>
                <td class="px-6 py-4 text-sm font-mono font-medium text-indigo-600">{{ $coupon->code }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ $coupon->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    @if($coupon->discount_type === 'percent')
                        {{ $coupon->discount_value }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $coupon->members_count > 0 ? $coupon->members_count . ' member' : 'Semua' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $coupon->products_count > 0 ? $coupon->products_count . ' produk' : 'Semua' }}
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $coupon->expired_at ? $coupon->expired_at->format('d M Y H:i') : '-' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.coupons.show', $coupon) }}" class="text-gray-600 hover:text-gray-800 text-sm font-medium">Detail</a>
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Hapus kupon ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">Belum ada kupon.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
