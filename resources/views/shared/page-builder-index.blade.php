{{--
    Partial daftar produk Page Builder — dipakai bersama admin & member.
    Wajib menerima: $products, $search, $indexUrl, $editRouteName.
--}}
<h1 class="text-2xl font-bold dk-heading mb-2">Page Builder</h1>
<p class="text-sm dk-text mb-6">Pilih produk yang mau dibuat atau diperbaiki landing page-nya, lalu susun halamannya dengan drag &amp; drop. Hasilnya otomatis mobile-friendly.</p>

<form method="GET" action="{{ $indexUrl }}" class="mb-6" style="max-width:420px;">
    <div style="display:flex; gap:8px;">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari produk..." class="dk-input" style="flex:1;">
        <button type="submit" class="dk-btn dk-btn-primary">Cari</button>
    </div>
</form>

@if($products->isEmpty())
    <div class="dk-card p-10 text-center">
        <p class="dk-text-muted">Tidak ada produk{{ $search !== '' ? ' untuk pencarian "'.$search.'"' : '' }}.</p>
    </div>
@else
<div class="dk-grid-3" style="gap:20px;">
    @foreach($products as $product)
        @php
            $lp = $product->landingPage;
            $thumbUrl = $product->thumbnail ? asset('storage/'.$product->thumbnail) : ($lp && $lp->hero_image ? asset('storage/'.$lp->hero_image) : null);
            $hasBuilder = $lp && $lp->builder_json;
        @endphp
        <div class="dk-card" style="overflow:hidden; display:flex; flex-direction:column;">
            <div style="aspect-ratio:16/9; background:#0d1326; position:relative;">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <div style="width:100%; height:100%; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center; padding:12px;">
                        <span style="color:#fff; font-weight:700; text-align:center;">{{ $product->title }}</span>
                    </div>
                @endif
                <div style="position:absolute; top:8px; left:8px; display:flex; gap:6px; flex-wrap:wrap;">
                    @if($hasBuilder)
                        <span class="dk-badge" style="background:rgba(99,102,241,0.9); color:#fff;">Dibuat via Builder</span>
                    @elseif($lp)
                        <span class="dk-badge" style="background:rgba(148,163,184,0.85); color:#0b1120;">LP Lama</span>
                    @else
                        <span class="dk-badge" style="background:rgba(30,41,59,0.85); color:#94a3b8;">Belum ada LP</span>
                    @endif
                    @if($lp && $lp->is_published)
                        <span class="dk-badge" style="background:rgba(16,185,129,0.9); color:#fff;">Published</span>
                    @endif
                </div>
            </div>
            <div style="padding:16px; display:flex; flex-direction:column; gap:12px; flex:1;">
                <div style="min-width:0;">
                    <div class="dk-heading" style="font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $product->title }}</div>
                    <div class="dk-text-muted" style="font-size:12px;">/p/{{ $product->slug }}</div>
                </div>
                <div style="display:flex; gap:8px; margin-top:auto;">
                    <a href="{{ route($editRouteName, $product) }}" class="dk-btn dk-btn-primary" style="flex:1; justify-content:center;">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        {{ $hasBuilder ? 'Edit Halaman' : 'Buat Halaman' }}
                    </a>
                    <a href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" class="dk-btn dk-btn-outline" title="Lihat halaman publik">
                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">
    {{ $products->links() }}
</div>
@endif

