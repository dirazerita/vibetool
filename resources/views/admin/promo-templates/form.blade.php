@extends('layouts.admin')
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
    @media (max-width: 1024px) { .pt-form { grid-template-columns: 1fr; } }
</style>

<div style="margin-bottom:18px;">
    <a href="{{ route('admin.promo-templates.index') }}" style="color:#94a3b8; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-bottom:8px;">
        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar
    </a>
    <h1 class="text-2xl font-bold dk-heading">{{ $template->exists ? 'Edit Template Promo' : 'Buat Template Promo' }}</h1>
</div>

<form method="POST" action="{{ $template->exists ? route('admin.promo-templates.update', $template) : route('admin.promo-templates.store') }}" x-data="promoTemplateForm()">
    @csrf
    @if($template->exists) @method('PUT') @endif

    <div class="pt-form">
        <div>
            <div class="pt-section">
                <label class="pt-label" for="title">Judul Template</label>
                <input type="text" id="title" name="title" class="pt-input" value="{{ old('title', $template->title) }}" maxlength="200" required placeholder="Mis. Ajakan rekrut affiliate / Promo Telegram launching produk">
                @error('title') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label" for="category">Kategori</label>
                <select id="category" name="category" class="pt-select" x-model="category" required>
                    <option value="member" @selected(old('category', $template->category) === 'member')>Promo Member — untuk rekrut affiliate baru</option>
                    <option value="product" @selected(old('category', $template->category) === 'product')>Promo Produk — untuk promosikan produk tertentu</option>
                </select>
                @error('category') <p class="pt-error">{{ $message }}</p> @enderror

                <div x-show="category === 'product'" x-cloak>
                    <label class="pt-label" for="product_id">Produk Terkait</label>
                    <select id="product_id" name="product_id" class="pt-select">
                        <option value="">— Pilih produk —</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $template->product_id) == $product->id)>{{ $product->title }}</option>
                        @endforeach
                    </select>
                    <p class="pt-help">Placeholder produk (<code>{nama_produk}</code> dll) akan diisi dari produk ini.</p>
                    @error('product_id') <p class="pt-error">{{ $message }}</p> @enderror
                </div>

                <label class="pt-label" for="body">Isi Template</label>
                <p class="pt-help" style="margin-bottom:8px;">Pakai placeholder di bawah — akan ter-replace otomatis dengan data member yang share.</p>
                <textarea id="body" name="body" x-model="body" class="pt-textarea" maxlength="5000" required placeholder="Tulis isi template di sini...">{{ old('body', $template->body) }}</textarea>
                @error('body') <p class="pt-error">{{ $message }}</p> @enderror

                <label class="pt-label">Placeholder yang Tersedia</label>
                <p class="pt-help">Klik untuk salin ke clipboard:</p>
                <div style="margin-top:8px;">
                    <template x-for="(desc, ph) in placeholders" :key="ph">
                        <span class="pt-placeholder-chip" @click="insertPlaceholder(ph)" :title="desc" x-text="ph"></span>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <div class="pt-section">
                <label class="pt-label">Pengaturan</label>
                <div class="pt-checkbox-row">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $template->is_active))>
                    <label for="is_active" style="color:#cbd5e1; font-size:14px; cursor:pointer;">Template aktif (muncul di halaman promo member)</label>
                </div>

                <label class="pt-label" for="order">Urutan</label>
                <input type="number" id="order" name="order" class="pt-input" value="{{ old('order', $template->order) }}" min="0" max="999">
                <p class="pt-help">Angka kecil tampil duluan. Default 0.</p>

                <button type="submit" class="dk-btn dk-btn-primary" style="margin-top:18px; width:100%; justify-content:center;">
                    {{ $template->exists ? 'Simpan Perubahan' : 'Buat Template' }}
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
function promoTemplateForm() {
    return {
        category: @json(old('category', $template->category ?? 'member')),
        body: @json(old('body', $template->body ?? '')),
        memberPlaceholders: @json(\App\Models\PromoTemplate::PLACEHOLDERS_MEMBER),
        productPlaceholders: @json(\App\Models\PromoTemplate::PLACEHOLDERS_PRODUCT),
        sampleData: {
            member: {
                '{nama_member}': 'Budi Susanto',
                '{kode_referral}': 'BUDI123',
                '{link_referral}': @json(rtrim(url('/'), '/').'?ref=BUDI123'),
            },
            product: {
                '{nama_member}': 'Budi Susanto',
                '{kode_referral}': 'BUDI123',
                '{link_referral}': @json(rtrim(url('/'), '/').'?ref=BUDI123'),
                '{nama_produk}': 'VibeTool Studio Pro',
                '{harga}': 'Rp 199.000',
                '{harga_coret}': 'Rp 299.000',
                '{link_produk}': @json(rtrim(url('/'), '/').'/p/vibetool-studio-pro?ref=BUDI123'),
                '{deskripsi}': 'Software desain video all-in-one untuk creator.',
            },
        },
        get placeholders() {
            return this.category === 'product' ? this.productPlaceholders : this.memberPlaceholders;
        },
        get previewText() {
            const data = this.sampleData[this.category] || {};
            let out = this.body || '';
            for (const k in data) {
                out = out.split(k).join(data[k]);
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
