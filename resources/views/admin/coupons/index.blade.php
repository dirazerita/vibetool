@extends('layouts.admin')
@section('title', 'Kelola Kupon')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold dk-heading">Kelola Kupon</h1>
    <a href="{{ route('admin.coupons.create') }}" class="dk-btn dk-btn-primary">Tambah Kupon</a>
</div>

<div class="dk-table">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Diskon</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Assign Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Assign Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Digunakan</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Expired</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coupons as $coupon)
            <tr>
                <td class="px-6 py-4 text-sm font-mono font-medium" style="color:#818cf8">{{ $coupon->code }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">{{ $coupon->name }}</td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">
                    @if($coupon->discount_type === 'percent')
                        {{ $coupon->discount_value }}%
                    @else
                        Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">
                    {{ $coupon->members_count > 0 ? $coupon->members_count . ' member' : 'Semua' }}
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">
                    {{ $coupon->products_count > 0 ? $coupon->products_count . ' produk' : 'Semua' }}
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->is_active ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"' }}">
                        {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm" style="color:#e2e8f0">
                    @if($coupon->max_uses)
                        <span style="color:#c4b5fd">{{ $coupon->used_count }}</span><span class="dk-text-muted"> / {{ $coupon->max_uses }}</span>
                    @else
                        <span style="color:#c4b5fd">{{ $coupon->used_count }}</span><span class="dk-text-muted"> kali</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm" style="color:#94a3b8">
                    {{ $coupon->expired_at ? $coupon->expired_at->format('d M Y H:i') : '-' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.coupons.show', $coupon) }}" class="dk-text hover:text-gray-800 text-sm font-medium">Detail</a>
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-sm font-medium" style="color:#818cf8">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Hapus kupon ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-medium" style="color:#fca5a5">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center" style="color:#64748b">Belum ada kupon.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
