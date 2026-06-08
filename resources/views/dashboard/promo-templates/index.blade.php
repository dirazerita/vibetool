@extends('layouts.dashboard')
@section('title', 'Template Promo Saya')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Template Promo Saya</h1>
        <p class="text-sm dk-text-muted mt-1">Template promosi untuk produk yang Anda upload. Setelah <strong>disetujui admin</strong>, template akan muncul di halaman <em>Promo &amp; Share</em> semua member untuk dishare.</p>
    </div>
    <a href="{{ route('dashboard.promo-templates.create') }}" class="dk-btn dk-btn-primary text-sm">+ Buat Template Baru</a>
</div>

@if(session('success'))
<div class="dk-card mb-4" style="padding:12px 16px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7">
    {{ session('success') }}
</div>
@endif

<div class="dk-card" style="padding:16px">
    <div class="dk-table">
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Produk</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Media</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Tanggal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td class="px-4 py-3" style="color:#e2e8f0">{{ $t->title }}</td>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $t->product->title ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($t->approval_status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(234,179,8,0.15);color:#fde047">Menunggu Review</span>
                        @elseif($t->approval_status === 'approved')
                            @if($t->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Disetujui &amp; Aktif</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(100,116,139,0.15);color:#cbd5e1">Disetujui (nonaktif)</span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background:rgba(239,68,68,0.15);color:#fca5a5" title="{{ $t->rejection_reason }}">Ditolak</span>
                        @endif
                        @if($t->approval_status === 'rejected' && $t->rejection_reason)
                            <div class="text-xs mt-1" style="color:#fca5a5">Alasan: {{ $t->rejection_reason }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $t->media_count }} file</td>
                    <td class="px-4 py-3" style="color:#94a3b8">{{ $t->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('dashboard.promo-templates.edit', $t) }}" class="dk-btn dk-btn-secondary text-xs">Edit</a>
                        <form method="POST" action="{{ route('dashboard.promo-templates.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus template ini? Aksi tidak bisa dibatalkan.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="dk-btn text-xs" style="background:rgba(239,68,68,0.15);color:#fca5a5">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center" style="color:#64748b">
                        Belum ada template promo. <a href="{{ route('dashboard.promo-templates.create') }}" style="color:#818cf8">Buat sekarang &rarr;</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $templates->links() }}</div>
</div>
@endsection
