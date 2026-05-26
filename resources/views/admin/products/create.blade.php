@extends('layouts.admin')
@section('title', 'Tambah Produk')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Tambah Produk</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="title" class="dk-label">Judul Produk</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full dk-input" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="dk-label">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" class="w-full dk-input">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="price" class="dk-label">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="w-full dk-input" required>
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_type" class="dk-label">Tipe Produk</label>
                    <select name="product_type" id="product_type" class="w-full dk-input" required>
                        <option value="digital" {{ old('product_type', 'digital') === 'digital' ? 'selected' : '' }}>Produk Digital (file/download)</option>
                        <option value="software" {{ old('product_type') === 'software' ? 'selected' : '' }}>Software / Tool (dengan lisensi)</option>
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">Pilih <strong>Software / Tool</strong> kalau produk ini butuh kunci lisensi yang akan diberikan otomatis ke member setelah membeli.</p>
                    @error('product_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="license-duration-section" style="{{ old('product_type') === 'software' ? '' : 'display:none;' }}">
                    <label for="license_duration" class="dk-label">Masa Berlaku Lisensi</label>
                    <select name="license_duration" id="license_duration" class="w-full dk-input">
                        <option value="1_month" {{ old('license_duration') === '1_month' ? 'selected' : '' }}>1 Bulan</option>
                        <option value="6_months" {{ old('license_duration') === '6_months' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="1_year" {{ old('license_duration') === '1_year' ? 'selected' : '' }}>1 Tahun</option>
                        <option value="lifetime" {{ old('license_duration', 'lifetime') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">Masa berlaku lisensi dihitung sejak order ditandai lunas. Pilih <strong>Lifetime</strong> jika lisensi tidak memiliki batas waktu.</p>
                    @error('license_duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="dk-card" style="padding:16px">
                    <h3 class="text-sm font-semibold dk-heading mb-1">Pengaturan Komisi</h3>
                    <p class="text-xs dk-text-muted mb-4">Member yang sudah pernah membeli produk ini biasanya dapat tarif lebih tinggi. Member yang ikut promosi tapi belum membeli produknya tetap dapat komisi, tapi lebih kecil.</p>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide dk-text mb-2">Komisi Affiliator (penjualan langsung dari user)</p>
                            <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                                <div>
                                    <label for="commission_percent" class="dk-label" style="font-size:12px">Sudah beli produk (%)</label>
                                    <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', 30) }}" step="0.01" class="w-full dk-input" required>
                                    @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="commission_percent_non_owner" class="dk-label" style="font-size:12px">Belum beli produk (%)</label>
                                    <input type="number" name="commission_percent_non_owner" id="commission_percent_non_owner" value="{{ old('commission_percent_non_owner', 15) }}" step="0.01" class="w-full dk-input" required>
                                    @error('commission_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide dk-text mb-2">Bonus Upline (penjualan dari downline)</p>
                            <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                                <div>
                                    <label for="upline_percent" class="dk-label" style="font-size:12px">Sudah beli produk (%)</label>
                                    <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', 10) }}" step="0.01" class="w-full dk-input" required>
                                    @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="upline_percent_non_owner" class="dk-label" style="font-size:12px">Belum beli produk (%)</label>
                                    <input type="number" name="upline_percent_non_owner" id="upline_percent_non_owner" value="{{ old('upline_percent_non_owner', 5) }}" step="0.01" class="w-full dk-input" required>
                                    @error('upline_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="thumbnail" class="dk-label">Thumbnail Produk (opsional)</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Gambar thumbnail produk. Maks 5MB.</p>
                    @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file" class="dk-label">File Produk</label>
                    <input type="file" name="file" id="file" class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Upload file produk (maks 100MB), <em>atau</em> isi link eksternal di bawah.</p>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file_url" class="dk-label">Link Eksternal (URL)</label>
                    <input type="url" name="file_url" id="file_url" value="{{ old('file_url') }}" placeholder="https://drive.google.com/..." class="w-full dk-input">
                    <p class="text-xs mt-1 dk-text-muted">Misal: Google Drive, Dropbox, dsb. Isi <strong>salah satu</strong> antara File Produk atau Link Eksternal.</p>
                    @error('file_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Simpan Produk</button>
                <a href="{{ route('admin.products.index') }}" class="dk-btn dk-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.getElementById('product_type').addEventListener('change', function() {
        document.getElementById('license-duration-section').style.display = this.value === 'software' ? '' : 'none';
    });
</script>
@endsection
