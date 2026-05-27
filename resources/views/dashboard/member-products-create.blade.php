@extends('layouts.dashboard')
@section('title', 'Upload Produk Baru')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Upload Produk Baru</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <div class="dk-alert-success" style="background:rgba(99,102,241,0.1); border-color:rgba(99,102,241,0.3); color:#a5b4fc; margin-bottom:20px;">
            <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Produk yang Anda upload akan ditinjau oleh admin terlebih dahulu sebelum dipublikasikan. Pengaturan komisi akan ditentukan oleh admin.
        </div>

        @if($errors->any())
            <div class="dk-alert-error" style="margin-bottom:20px;">
                <ul style="list-style:disc; padding-left:16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.member-products.store') }}" enctype="multipart/form-data">
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
                    <label for="product_type" class="dk-label">Tipe Produk</label>
                    <select name="product_type" id="product_type" class="w-full dk-input" required>
                        <option value="digital" {{ old('product_type', 'digital') === 'digital' ? 'selected' : '' }}>Produk Digital (file/download)</option>
                        <option value="software" {{ old('product_type') === 'software' ? 'selected' : '' }}>Software / Tool (dengan lisensi)</option>
                        <option value="free" {{ old('product_type') === 'free' ? 'selected' : '' }}>Produk Gratis (Software via login akun)</option>
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">
                        <strong>Software / Tool:</strong> kunci lisensi unik dikirim otomatis ke member.<br>
                        <strong>Produk Gratis:</strong> harga otomatis 0, member klaim langsung tanpa checkout.
                    </p>
                    @error('product_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="price-section" style="{{ old('product_type') === 'free' ? 'display:none;' : '' }}">
                    <label for="price" class="dk-label">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="w-full dk-input">
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="price-free-notice" style="{{ old('product_type') === 'free' ? '' : 'display:none;' }}">
                    <div class="dk-card" style="padding:12px; border-left:4px solid #10b981;">
                        <p class="text-sm font-semibold" style="color:#10b981;">Harga: GRATIS</p>
                        <p class="text-xs dk-text-muted mt-1">Member tidak perlu bayar.</p>
                    </div>
                </div>

                <div id="license-duration-section" style="{{ old('product_type') === 'software' ? '' : 'display:none;' }}">
                    <label for="license_duration" class="dk-label">Masa Berlaku Lisensi</label>
                    <select name="license_duration" id="license_duration" class="w-full dk-input">
                        <option value="1_month" {{ old('license_duration') === '1_month' ? 'selected' : '' }}>1 Bulan</option>
                        <option value="6_months" {{ old('license_duration') === '6_months' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="1_year" {{ old('license_duration') === '1_year' ? 'selected' : '' }}>1 Tahun</option>
                        <option value="lifetime" {{ old('license_duration', 'lifetime') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                    @error('license_duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                <button type="submit" class="dk-btn dk-btn-primary">Submit Produk</button>
                <a href="{{ route('dashboard.member-products') }}" class="dk-btn dk-btn-outline">Batal</a>
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
        var priceInput = document.getElementById('price');

        if (type === 'free') {
            priceSection.style.display = 'none';
            priceFreeNotice.style.display = '';
            priceInput.removeAttribute('required');
            priceInput.value = '0';
        } else {
            priceSection.style.display = '';
            priceFreeNotice.style.display = 'none';
            priceInput.setAttribute('required', 'required');
        }

        licenseSection.style.display = type === 'software' ? '' : 'none';
    }
    document.getElementById('product_type').addEventListener('change', toggleProductTypeUI);
    toggleProductTypeUI();
</script>
@endsection
