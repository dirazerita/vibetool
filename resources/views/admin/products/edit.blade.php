@extends('layouts.admin')
@section('title', 'Edit Produk')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Edit Produk: {{ $product->title }}</h1>

<div class="mb-6 " style="border-bottom:1px solid #1e2b3d">
    <nav class="flex space-x-8">
        <a href="{{ route('admin.products.edit', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid #6366f1;color:#a5b4fc">Produk</a>
        <a href="{{ route('admin.products.landing-page', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Landing Page</a>
        <a href="{{ route('admin.products.video-tutorials', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Video Tutorial</a>
    </nav>
</div>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="title" class="dk-label">Judul Produk</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $product->title) }}" class="w-full dk-input" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="dk-label">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" class="w-full dk-input">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_type" class="dk-label">Tipe Produk</label>
                    <select name="product_type" id="product_type" class="w-full dk-input" required>
                        <option value="digital" {{ old('product_type', $product->product_type ?? 'digital') === 'digital' ? 'selected' : '' }}>Produk Digital (file/download)</option>
                        <option value="software" {{ old('product_type', $product->product_type) === 'software' ? 'selected' : '' }}>Software / Tool (dengan lisensi)</option>
                        <option value="free" {{ old('product_type', $product->product_type) === 'free' ? 'selected' : '' }}>Produk Gratis (Software via login akun)</option>
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">
                        <strong>Software / Tool:</strong> kunci lisensi unik dikirim otomatis ke member.<br>
                        <strong>Produk Gratis:</strong> harga otomatis 0, member klaim langsung tanpa checkout. Software ini divalidasi pakai email + password akun VibeTool.Id member.
                    </p>
                    @error('product_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="price-section" style="{{ old('product_type', $product->product_type) === 'free' ? 'display:none;' : '' }}">
                    <label for="price" class="dk-label">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" class="w-full dk-input">
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="price-free-notice" style="{{ old('product_type', $product->product_type) === 'free' ? '' : 'display:none;' }}">
                    <div class="dk-card" style="padding:12px; border-left:4px solid #10b981;">
                        <p class="text-sm font-semibold" style="color:#10b981;">Harga: GRATIS</p>
                        <p class="text-xs dk-text-muted mt-1">Member tidak perlu bayar. Tombol "Beli Sekarang" akan otomatis berubah jadi "Dapatkan Gratis".</p>
                    </div>
                </div>

                <div id="license-duration-section" style="{{ old('product_type', $product->product_type) === 'software' ? '' : 'display:none;' }}">
                    <label for="license_duration" class="dk-label">Masa Berlaku Lisensi</label>
                    <select name="license_duration" id="license_duration" class="w-full dk-input">
                        <option value="1_month" {{ old('license_duration', $product->license_duration) === '1_month' ? 'selected' : '' }}>1 Bulan</option>
                        <option value="6_months" {{ old('license_duration', $product->license_duration) === '6_months' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="1_year" {{ old('license_duration', $product->license_duration) === '1_year' ? 'selected' : '' }}>1 Tahun</option>
                        <option value="lifetime" {{ old('license_duration', $product->license_duration ?? 'lifetime') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">Masa berlaku lisensi dihitung sejak order ditandai lunas. Pilih <strong>Lifetime</strong> jika lisensi tidak memiliki batas waktu.</p>
                    @error('license_duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="commission-section" class="dk-card" style="padding:16px; {{ old('product_type', $product->product_type ?? 'digital') === 'free' ? 'display:none;' : '' }}">
                    <h3 class="text-sm font-semibold dk-heading mb-1">Pengaturan Komisi</h3>
                    <p class="text-xs dk-text-muted mb-4">Member yang sudah pernah membeli produk ini biasanya dapat tarif lebih tinggi. Member yang ikut promosi tapi belum membeli produknya tetap dapat komisi, tapi lebih kecil.</p>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide dk-text mb-2">Komisi Affiliator (penjualan langsung dari user)</p>
                            <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                                <div>
                                    <label for="commission_percent" class="dk-label" style="font-size:12px">Sudah beli produk (%)</label>
                                    <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', $product->commission_percent) }}" step="0.01" class="w-full dk-input" required>
                                    @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="commission_percent_non_owner" class="dk-label" style="font-size:12px">Belum beli produk (%)</label>
                                    <input type="number" name="commission_percent_non_owner" id="commission_percent_non_owner" value="{{ old('commission_percent_non_owner', $product->commission_percent_non_owner ?? $product->commission_percent) }}" step="0.01" class="w-full dk-input" required>
                                    @error('commission_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide dk-text mb-2">Bonus Upline (penjualan dari downline)</p>
                            <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                                <div>
                                    <label for="upline_percent" class="dk-label" style="font-size:12px">Sudah beli produk (%)</label>
                                    <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', $product->upline_percent) }}" step="0.01" class="w-full dk-input" required>
                                    @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="upline_percent_non_owner" class="dk-label" style="font-size:12px">Belum beli produk (%)</label>
                                    <input type="number" name="upline_percent_non_owner" id="upline_percent_non_owner" value="{{ old('upline_percent_non_owner', $product->upline_percent_non_owner ?? $product->upline_percent) }}" step="0.01" class="w-full dk-input" required>
                                    @error('upline_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide dk-text mb-2">Bagian Pembuat Produk</p>
                            <div>
                                <label for="creator_share_percent" class="dk-label" style="font-size:12px">
                                    Persentase untuk pembuat produk (%)
                                    @if($product->created_by && $product->creator)
                                        <span class="dk-text-muted">— pembuat: <strong>{{ $product->creator->name }}</strong></span>
                                    @endif
                                </label>
                                <input type="number" name="creator_share_percent" id="creator_share_percent" value="{{ old('creator_share_percent', $product->creator_share_percent ?? 0) }}" step="0.01" min="0" max="100" class="w-full dk-input">
                                <p class="text-xs mt-1 dk-text-muted">
                                    @if($product->created_by)
                                        Dibayar ke pembuat <strong>setiap kali produk terjual</strong>, sebagai tambahan komisi affiliate/upline. Set <code>0</code> untuk skip.
                                    @else
                                        Produk ini di-upload oleh admin (bukan member), jadi field ini tidak dipakai. Set <code>0</code>.
                                    @endif
                                </p>
                                @error('creator_share_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="dk-checkbox">
                        <span class="ml-2 text-sm dk-text">Produk Aktif</span>
                    </label>
                </div>

                <div>
                    <label for="thumbnail" class="dk-label">Thumbnail Produk</label>
                    @if($product->thumbnail)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="Thumbnail" class="rounded-lg object-cover" style="width: 120px; height: 120px;">
                        </div>
                    @endif
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Maks 5MB. Kosongkan jika tidak ingin mengubah.</p>
                    @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file" class="dk-label">File Produk (kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" name="file" id="file" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">
                        @if($product->file_path)
                            File saat ini: {{ $product->file_path }}
                        @else
                            Belum ada file produk.
                        @endif
                    </p>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file_url" class="dk-label">Link Eksternal (URL)</label>
                    <input type="url" name="file_url" id="file_url" value="{{ old('file_url', $product->file_url) }}" placeholder="https://drive.google.com/..." class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Misal: Google Drive, Dropbox, dsb. Jika diisi, member akan diarahkan ke link ini saat klik tombol download. Kosongkan untuk tetap pakai file yang di-upload.</p>
                    @error('file_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Update Produk</button>
                <a href="{{ route('admin.products.index') }}" class="dk-btn dk-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleProductTypeUI() {
        var type = document.getElementById('product_type').value;
        var priceSection = document.getElementById('price-section');
        var priceFreeNotice = document.getElementById('price-free-notice');
        var licenseSection = document.getElementById('license-duration-section');
        var commissionSection = document.getElementById('commission-section');
        var priceInput = document.getElementById('price');
        var commissionInputs = ['commission_percent','commission_percent_non_owner','upline_percent','upline_percent_non_owner'];

        if (type === 'free') {
            priceSection.style.display = 'none';
            priceFreeNotice.style.display = '';
            priceInput.removeAttribute('required');
            priceInput.value = '0';
            commissionSection.style.display = 'none';
            commissionInputs.forEach(function(id){
                var el = document.getElementById(id);
                if (el) el.removeAttribute('required');
            });
        } else {
            priceSection.style.display = '';
            priceFreeNotice.style.display = 'none';
            priceInput.setAttribute('required', 'required');
            commissionSection.style.display = '';
            commissionInputs.forEach(function(id){
                var el = document.getElementById(id);
                if (el) el.setAttribute('required', 'required');
            });
        }

        licenseSection.style.display = type === 'software' ? '' : 'none';
    }
    document.getElementById('product_type').addEventListener('change', toggleProductTypeUI);
    toggleProductTypeUI();
</script>
@endsection
