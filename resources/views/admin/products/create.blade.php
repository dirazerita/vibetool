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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="commission_percent" class="block text-sm font-medium text-gray-700 mb-1">Komisi Affiliator (%)</label>
                        <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', 30) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="upline_percent" class="block text-sm font-medium text-gray-700 mb-1">Bonus Upline (%)</label>
                        <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', 10) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                    <input type="file" name="file" id="file" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
