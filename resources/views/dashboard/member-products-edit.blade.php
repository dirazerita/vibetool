@extends('layouts.dashboard')
@section('title', 'Edit Produk')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Edit Produk: {{ $product->title }}</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        @if($product->isRejected() && $product->rejection_reason)
            <div class="dk-alert-error" style="margin-bottom:20px;">
                <strong>Produk ditolak oleh admin:</strong> {{ $product->rejection_reason }}<br>
                <span class="text-xs">Perbaiki produk lalu submit ulang. Produk akan otomatis masuk antrian review kembali.</span>
            </div>
        @endif

        @if($errors->any())
            <div class="dk-alert-error" style="margin-bottom:20px;">
                <ul style="list-style:disc; padding-left:16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.member-products.update', $product) }}" enctype="multipart/form-data">
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
                    @error('license_duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                    <p class="text-xs mt-1 dk-text-muted">Misal: Google Drive, Dropbox, dsb.</p>
                    @error('file_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Update Produk</button>
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
