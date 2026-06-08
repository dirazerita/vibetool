@extends('layouts.admin')
@section('title', 'Template Promo')

@section('content')
<style>
    .pt-tabs { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
    .pt-tab { display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:9999px; background:#151e2d; border:1px solid #2d3a4a; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500; }
    .pt-tab.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .pt-tab .count { background:rgba(255,255,255,0.18); padding:1px 8px; border-radius:9999px; font-size:11px; font-weight:600; }
    .pt-row { display:flex; align-items:flex-start; gap:14px; padding:14px 16px; background:#151e2d; border:1px solid #2d3a4a; border-radius:12px; margin-bottom:10px; }
    .pt-row .title { font-weight:600; color:#e2e8f0; font-size:15px; }
    .pt-row .meta { font-size:12px; color:#94a3b8; margin-top:3px; }
    .pt-row .body-snippet { color:#94a3b8; font-size:13px; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .pt-pill-cat { display:inline-block; font-size:11px; font-weight:600; padding:2px 10px; border-radius:9999px; }
    .pt-pill-cat.member { background:rgba(79,70,229,0.18); color:#a5b4fc; }
    .pt-pill-cat.product { background:rgba(16,185,129,0.18); color:#6ee7b7; }
    .pt-pill-status { display:inline-block; font-size:11px; font-weight:600; padding:2px 10px; border-radius:9999px; margin-left:6px; }
    .pt-pill-status.on { background:rgba(16,185,129,0.18); color:#6ee7b7; }
    .pt-pill-status.off { background:rgba(100,116,139,0.18); color:#94a3b8; }
    .pt-actions { display:flex; gap:8px; flex-shrink:0; }
    .pt-actions a, .pt-actions button { background:#1e2b3d; border:1px solid #2d3a4a; color:#cbd5e1; padding:6px 12px; border-radius:8px; font-size:12px; text-decoration:none; cursor:pointer; }
    .pt-actions a:hover, .pt-actions button:hover { border-color:#475569; }
    .pt-actions .danger { color:#fca5a5; border-color:rgba(239,68,68,0.3); }
</style>

<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Template Promo</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Buat template promosi yang bisa di-copy & share oleh member. Pakai placeholder seperti <code style="color:#a5b4fc;">{nama_member}</code> & <code style="color:#a5b4fc;">{link_referral}</code> — akan ter-replace otomatis per member.</p>
    </div>
    <a href="{{ route('admin.promo-templates.create') }}" class="dk-btn dk-btn-primary">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Template
    </a>
</div>

@if(session('success'))
    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:10px 14px; border-radius:10px; margin-bottom:14px; font-size:14px;">{{ session('success') }}</div>
@endif

<div class="pt-tabs">
    <a href="{{ route('admin.promo-templates.index') }}" class="pt-tab {{ $category === '' ? 'active' : '' }}">Semua <span class="count">{{ $counts['all'] }}</span></a>
    <a href="{{ route('admin.promo-templates.index', ['category' => 'member']) }}" class="pt-tab {{ $category === 'member' ? 'active' : '' }}">Promo Member <span class="count">{{ $counts['member'] }}</span></a>
    <a href="{{ route('admin.promo-templates.index', ['category' => 'product']) }}" class="pt-tab {{ $category === 'product' ? 'active' : '' }}">Promo Produk <span class="count">{{ $counts['product'] }}</span></a>
</div>

@forelse($templates as $t)
    <div class="pt-row">
        <div style="flex:1; min-width:0;">
            <div class="title">{{ $t->title }}</div>
            <div class="meta">
                <span class="pt-pill-cat {{ $t->category }}">{{ $t->categoryLabel() }}</span>
                @if($t->category === 'product' && $t->product)
                    <span style="color:#cbd5e1;">· {{ $t->product->title }}</span>
                @endif
                <span class="pt-pill-status {{ $t->is_active ? 'on' : 'off' }}">{{ $t->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <span style="color:#64748b; margin-left:6px;">· urutan {{ $t->order }}</span>
            </div>
            <div class="body-snippet">{{ \Illuminate\Support\Str::limit(str_replace(["\r", "\n"], ' ', $t->body), 140) }}</div>
        </div>
        <div class="pt-actions">
            <a href="{{ route('admin.promo-templates.edit', $t) }}">Edit</a>
            <form method="POST" action="{{ route('admin.promo-templates.destroy', $t) }}" onsubmit="return confirm('Hapus template ini?');" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="danger">Hapus</button>
            </form>
        </div>
    </div>
@empty
    <div style="text-align:center; padding:36px 16px; color:#94a3b8; background:#151e2d; border:1px dashed #2d3a4a; border-radius:12px;">
        Belum ada template promo. Klik <strong>Buat Template</strong> untuk membuat template pertama.
    </div>
@endforelse

<div style="margin-top:16px;">{{ $templates->links() }}</div>
@endsection
