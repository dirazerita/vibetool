@extends('layouts.dashboard')
@section('title', $template->exists ? 'Edit Template Promo' : 'Buat Template Promo')

@section('content')
<style>
    .pt-form { display:grid; grid-template-columns: 2fr 1fr; gap:24px; }
    .pt-section { background:#0f1729; border:1px solid #1e2b3d; border-radius:14px; padding:20px; margin-bottom:16px; }
    .pt-label { display:block; font-weight:600; color:#e2e8f0; font-size:14px; margin-bottom:6px; margin-top:14px; }
    .pt-label:first-child { margin-top:0; }
    .pt-help { font-size:12px; color:#94a3b8; margin-top:4px; line-height:1.5; }
    .pt-input, .pt-textarea, .pt-select { width:100%; background:#151e2d; border:1px solid #2d3a4a; color:#e2e8f0; padding:10px 12px; border-radius:8px; font-size:14px; font-family:inherit; }
    .pt-input:focus, .pt-textarea:focus, .pt-select:focus { outline:none; border-color:#6366f1; }
    .pt-textarea { resize:vertical; min-height:180px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:13px; line-height:1.6; }
    .pt-error { color:#fca5a5; font-size:12px; margin-top:4px; }
    .pt-checkbox-row { display:flex; align-items:center; gap:10px; margin-top:8px; }
    .pt-checkbox-row input[type=checkbox] { width:18px; height:18px; accent-color:#6366f1; }
    .pt-placeholder-chip { display:inline-block; padding:4px 10px; background:#1e2b3d; border:1px solid #2d3a4a; border-radius:9999px; font-size:12px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; color:#a5b4fc; cursor:pointer; margin:3px 4px 3px 0; }
    .pt-placeholder-chip:hover { background:#2d3a4a; }
    .pt-preview { background:#151e2d; border:1px solid #2d3a4a; border-radius:8px; padding:14px; white-space:pre-wrap; color:#e2e8f0; font-size:14px; line-height:1.6; min-height:140px; word-break:break-word; }
    .pt-file-input { width:100%; background:#151e2d; border:1px dashed #2d3a4a; color:#cbd5e1; padding:10px 12px; border-radius:8px; font-size:13px; cursor:pointer; }
    .pt-media-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:10px; margin-top:10px; }
    .pt-media-card { position:relative; background:#0b1120; border:1px solid #2d3a4a; border-radius:8px; overflow:hidden; }
    .pt-media-thumb { width:100%; height:110px; object-fit:cover; display:block; background:#000; }
    .pt-media-meta { padding:6px 8px; font-size:11px; color:#94a3b8; display:flex; justify-content:space-between; gap:4px; }
    .pt-media-delete { position:absolute; top:4px; right:4px; background:rgba(239,68,68,0.92); color:#fff; border:none; border-radius:6px; padding:4px 8px; font-size:11px; cursor:pointer; font-weight:600; }
    .pt-media-delete:hover { background:#dc2626; }
    .pt-media-type-badge { position:absolute; top:4px; left:4px; background:rgba(0,0,0,0.65); color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:6px; letter-spacing:.5px; }
    @media (max-width: 1024px) { .pt-form { grid-template-columns: 1fr; } }
</style>

<div style="margin-bottom:18px;">
    <a href="{{ route('dashboard.promo-templates.index') }}" style="color:#94a3b8; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-bottom:8px;">
        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar
    </a>
    <h1 class="text-2xl font-bold dk-heading">{{ $template->exists ? 'Edit Template Promo' : 'Buat Template Promo' }}</h1>
    @if($template->exists)
        @if($template->approval_status === 'pending')
            <p class="text-sm mt-1" style="color:#fde047">Status: Menunggu review admin. Setelah disetujui, template akan tampil di halaman promo semua member.</p>
        @elseif($template->approval_status === 'rejected')
            <p class="text-sm mt-1" style="color:#fca5a5">Status: Ditolak admin. @if($template->rejection_reason) Alasan: {{ $template->rejection_reason }}. @endif Edit dan simpan ulang untuk kirim ulang ke review.</p>
        @else
            <p class="text-sm mt-1" style="color:#6ee7b7">Status: Disetujui. Edit dan simpan ulang akan mengembalikan ke status menunggu review.</p>
        @endif
    @else
        <p class="text-sm dk-text-muted mt-1">Buat template promo untuk produk yang Anda upload. Setelah dikirim, admin akan review dulu sebelum tampil di halaman promo public.</p>
    @endif
</div>

<form method="POST" action="{{ $template->exists ? route('dashboard.promo-templates.update', $template) : route('dashboard.promo-templates.store') }}" enctype="multipart/form-data" x-data="memberPromoTemplateForm()">
    @csrf
    @if($template->exists) @method('PUT') @endif

    <div class="pt-form">
        <div>
            <div class="pt-section">
                <label class="pt-label" for="title">Judul Template</label>
                <input type="text" id="title" name="title" class="pt-input" value="{{ old('title', $template->title) }}" maxlength="200" required placeholder="Mis. Promo launching produk saya">
                @error('title') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label" for="product_id">Produk</label>
                @if($products->isEmpty())
                    <p class="pt-help" style="color:#fca5a5">Anda belum punya produk yang sudah disetujui admin. <a href="{{ route('dashboard.member-products.create') }}" style="color:#818cf8">Upload produk dulu &rarr;</a></p>
                @else
                    <select id="product_id" name="product_id" class="pt-select" required>
                        <option value="">— Pilih produk Anda —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $template->product_id) == $product->id)>{{ $product->title }}</option>
                        @endforeach
                    </select>
                    <p class="pt-help">Hanya produk yang Anda upload sendiri yang muncul di sini.</p>
                @endif
                @error('product_id') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label" for="body">Isi Template</label>
                <p class="pt-help" style="margin-bottom:8px;">Pakai placeholder di bawah — akan otomatis ter-replace dengan data member yang share.</p>
                <textarea id="body" name="body" x-model="body" class="pt-textarea" maxlength="5000" required placeholder="Tulis isi template di sini...">{{ old('body', $template->body) }}</textarea>
                @error('body') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label">Placeholder yang Tersedia</label>
                <p class="pt-help">Klik untuk insert ke teks:</p>
                <div style="margin-top:8px;">
                    <template x-for="(desc, ph) in placeholders" :key="ph">
                        <span class="pt-placeholder-chip" @click="insertPlaceholder(ph)" :title="desc" x-text="ph"></span>
                    </template>
                </div>
            </div>

            <div class="pt-section">
                <label class="pt-label">Lampiran Gambar &amp; Video (opsional)</label>
                <p class="pt-help" style="margin-bottom:10px;">Member yang share template Anda bisa download &amp; share file ini bareng teks template. Maks {{ \App\Http\Controllers\Admin\PromoTemplateController::MAX_MEDIA_PER_TEMPLATE }} file total.</p>

                @if($template->exists && $template->media->isNotEmpty())
                    <div style="margin-bottom:14px;">
                        <p style="font-size:12px; color:#94a3b8; font-weight:600; margin-bottom:6px;">File yang sudah diupload:</p>
                        <div class="pt-media-grid">
                            @foreach($template->media as $media)
                                <div class="pt-media-card">
                                    <span class="pt-media-type-badge">{{ strtoupper($media->type) }}</span>
                                    @if($media->isImage())
                                        <a href="{{ $media->url() }}" target="_blank" rel="noopener">
                                            <img src="{{ $media->url() }}" alt="{{ $media->original_name }}" class="pt-media-thumb" loading="lazy">
                                        </a>
                                    @else
                                        <video src="{{ $media->url() }}" class="pt-media-thumb" controls preload="metadata"></video>
                                    @endif
                                    <div class="pt-media-meta">
                                        <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $media->original_name }}">{{ $media->original_name }}</span>
                                        <span>{{ $media->humanSize() }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('dashboard.promo-templates.media.destroy', [$template, $media]) }}" onsubmit="return confirm('Hapus file ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="pt-media-delete">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <label class="pt-label" for="images" style="margin-top:8px;">Tambah Gambar</label>
                <input type="file" id="images" name="images[]" class="pt-file-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                <p class="pt-help">JPG/PNG/WebP/GIF, maks {{ (int) (\App\Http\Controllers\Admin\PromoTemplateController::MAX_IMAGE_KB / 1024) }} MB per file.</p>
                @error('images') <p class="pt-error">{{ $message }}</p> @enderror
                @error('images.*') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label" for="videos">Tambah Video</label>
                <input type="file" id="videos" name="videos[]" class="pt-file-input" accept="video/mp4,video/webm,video/quicktime" multiple>
                <p class="pt-help">MP4/WebM/MOV, maks {{ (int) (\App\Http\Controllers\Admin\PromoTemplateController::MAX_VIDEO_KB / 1024) }} MB per file.</p>
                @error('videos') <p class="pt-error">{{ $message }}</p> @enderror
                @error('videos.*') <p class="pt-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <div class="pt-section">
                <label class="pt-label">Pengaturan</label>
                <div class="pt-checkbox-row">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $template->is_active))>
                    <label for="is_active" style="color:#cbd5e1; font-size:14px; cursor:pointer;">Template aktif</label>
                </div>
                <p class="pt-help">Hanya template <em>aktif &amp; disetujui admin</em> yang muncul di halaman promo public.</p>

                <label class="pt-label" for="order">Urutan</label>
                <input type="number" id="order" name="order" class="pt-input" value="{{ old('order', $template->order) }}" min="0" max="999">
                <p class="pt-help">Angka kecil tampil duluan. Default 0.</p>

                <button type="submit" class="dk-btn dk-btn-primary" style="margin-top:18px; width:100%; justify-content:center;" @disabled($products->isEmpty() && !$template->exists)>
                    {{ $template->exists ? 'Simpan Perubahan' : 'Kirim untuk Review Admin' }}
                </button>
            </div>

            <div class="pt-section">
                <label class="pt-label">Preview</label>
                <p class="pt-help" style="margin-bottom:8px;">Tampilan template (dengan contoh data member):</p>
                <div class="pt-preview" x-html="previewText"></div>
            </div>
        </div>
    </div>
</form>

<script>
function memberPromoTemplateForm() {
    return {
        body: @json(old('body', $template->body ?? '')),
        placeholders: @json(\App\Models\PromoTemplate::PLACEHOLDERS_PRODUCT),
        sampleData: {
            '{nama_member}': 'Budi Susanto',
            '{kode_referral}': 'BUDI123',
            '{link_referral}': @json(rtrim(url('/'), '/').'?ref=BUDI123'),
            '{nama_produk}': 'Produk Anda',
            '{harga}': 'Rp 199.000',
            '{harga_coret}': 'Rp 299.000',
            '{link_produk}': @json(rtrim(url('/'), '/').'/p/produk-anda?ref=BUDI123'),
            '{deskripsi}': 'Deskripsi singkat produk Anda akan tampil di sini.',
        },
        get previewText() {
            let out = this.body || '';
            for (const k in this.sampleData) {
                out = out.split(k).join(this.sampleData[k]);
            }
            return this.escapeHtml(out).replace(/(https?:\/\/[^\s]+)/g, '<span style="color:#a5b4fc;">$1</span>');
        },
        escapeHtml(s) {
            return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
        },
        insertPlaceholder(ph) {
            const ta = document.getElementById('body');
            if (!ta) return;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            this.body = this.body.slice(0, start) + ph + this.body.slice(end);
            ta.focus();
            this.$nextTick(() => { ta.selectionStart = ta.selectionEnd = start + ph.length; });
        },
    };
}
</script>
@endsection
