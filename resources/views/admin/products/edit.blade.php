@extends('layouts.admin')
@section('title', 'Edit Produk')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Produk: {{ $product->title }}</h1>

<div class="mb-6 border-b border-gray-200">
    <nav class="flex space-x-8">
        <a href="{{ route('admin.products.edit', $product) }}" class="border-b-2 border-indigo-500 text-indigo-600 px-1 pb-3 text-sm font-medium">Produk</a>
        <a href="{{ route('admin.products.landing-page', $product) }}" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 pb-3 text-sm font-medium">Landing Page</a>
    </nav>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Produk</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $product->title) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="commission_percent" class="block text-sm font-medium text-gray-700 mb-1">Komisi Affiliator (%)</label>
                        <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', $product->commission_percent) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="upline_percent" class="block text-sm font-medium text-gray-700 mb-1">Bonus Upline (%)</label>
                        <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', $product->upline_percent) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Produk Aktif</span>
                    </label>
                </div>

                <div>
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail Produk</label>
                    @if($product->thumbnail)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="Thumbnail" class="rounded-lg object-cover" style="width: 120px; height: 120px;">
                        </div>
                    @endif
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Maks 5MB. Kosongkan jika tidak ingin mengubah.</p>
                    @error('thumbnail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File Produk (kosongkan jika tidak ingin mengubah)</label>
                    <input type="file" name="file" id="file" class="w-full border-gray-300 rounded-lg shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">File saat ini: {{ $product->file_path }}</p>
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Update Produk</button>
                <a href="{{ route('admin.products.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
