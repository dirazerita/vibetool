@extends('layouts.admin')
@section('title', 'Tambah Produk')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Tambah Produk</h1>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Produk</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Produk</label>
                    <select name="product_type" id="product_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="digital" {{ old('product_type', 'digital') === 'digital' ? 'selected' : '' }}>Produk Digital (file/download)</option>
                        <option value="software" {{ old('product_type') === 'software' ? 'selected' : '' }}>Software / Tool (dengan lisensi)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih <strong>Software / Tool</strong> kalau produk ini butuh kunci lisensi yang akan diberikan otomatis ke member setelah membeli.</p>
                    @error('product_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Pengaturan Komisi</h3>
                    <p class="text-xs text-gray-500 mb-4">Member yang sudah pernah membeli produk ini biasanya dapat tarif lebih tinggi. Member yang ikut promosi tapi belum membeli produknya tetap dapat komisi, tapi lebih kecil.</p>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Komisi Affiliator (penjualan langsung dari user)</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="commission_percent" class="block text-xs font-medium text-gray-600 mb-1">Sudah beli produk (%)</label>
                                    <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', 30) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="commission_percent_non_owner" class="block text-xs font-medium text-gray-600 mb-1">Belum beli produk (%)</label>
                                    <input type="number" name="commission_percent_non_owner" id="commission_percent_non_owner" value="{{ old('commission_percent_non_owner', 15) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @error('commission_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Bonus Upline (penjualan dari downline)</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="upline_percent" class="block text-xs font-medium text-gray-600 mb-1">Sudah beli produk (%)</label>
                                    <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', 10) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="upline_percent_non_owner" class="block text-xs font-medium text-gray-600 mb-1">Belum beli produk (%)</label>
                                    <input type="number" name="upline_percent_non_owner" id="upline_percent_non_owner" value="{{ old('upline_percent_non_owner', 5) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    @error('upline_percent_non_owner') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail Produk (opsional)</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Gambar thumbnail produk. Maks 5MB.</p>
                    @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File Produk</label>
                    <input type="file" name="file" id="file" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Upload file produk (maks 100MB), <em>atau</em> isi link eksternal di bawah.</p>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file_url" class="block text-sm font-medium text-gray-700 mb-1">Link Eksternal (URL)</label>
                    <input type="url" name="file_url" id="file_url" value="{{ old('file_url') }}" placeholder="https://drive.google.com/..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Misal: Google Drive, Dropbox, dsb. Isi <strong>salah satu</strong> antara File Produk atau Link Eksternal.</p>
                    @error('file_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Simpan Produk</button>
                <a href="{{ route('admin.products.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
