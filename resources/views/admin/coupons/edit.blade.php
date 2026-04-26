@extends('layouts.admin')
@section('title', 'Edit Kupon')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Kupon</h1>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Kupon</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $coupon->code) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 uppercase" required>
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama / Deskripsi</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $coupon->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon</label>
                        <select name="discount_type" id="discount_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="percent" {{ old('discount_type', $coupon->discount_type) === 'percent' ? 'selected' : '' }}>Persen (%)</option>
                            <option value="fixed" {{ old('discount_type', $coupon->discount_type) === 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                        @error('discount_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon</label>
                        <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        @error('discount_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_purchase" class="block text-sm font-medium text-gray-700 mb-1">Minimal Pembelian (Rp)</label>
                        <input type="number" name="min_purchase" id="min_purchase" value="{{ old('min_purchase', $coupon->min_purchase) }}" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('min_purchase') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-1">Maksimal Penggunaan</label>
                        <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" min="1" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Kosongkan = unlimited">
                        @error('max_uses') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="expired_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Expired</label>
                    <input type="datetime-local" name="expired_at" id="expired_at" value="{{ old('expired_at', $coupon->expired_at ? $coupon->expired_at->format('Y-m-d\TH:i') : '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('expired_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="members" class="block text-sm font-medium text-gray-700 mb-1">Assign ke Member (kosongkan = semua member)</label>
                    <select name="members[]" id="members" multiple class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" style="min-height: 120px;">
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ in_array($member->id, old('members', $coupon->members->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih beberapa member</p>
                    @error('members') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="products" class="block text-sm font-medium text-gray-700 mb-1">Assign ke Produk (kosongkan = semua produk)</label>
                    <select name="products[]" id="products" multiple class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" style="min-height: 120px;">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ in_array($product->id, old('products', $coupon->products->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $product->title }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih beberapa produk</p>
                    @error('products') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Kupon Aktif</span>
                    </label>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Sudah digunakan: <strong>{{ $coupon->used_count }}</strong> kali</p>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Update Kupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
