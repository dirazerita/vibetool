@extends('layouts.admin')
@section('title', 'Tambah Komisi Khusus')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Tambah Komisi Khusus</h1>

@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.member-commissions.store') }}">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Member</label>
                    <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">-- Pilih Member --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('user_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Produk</label>
                    <p class="text-xs text-gray-500 mb-3">Centang satu atau beberapa produk yang ingin di-set komisi khususnya.</p>
                    <div class="flex items-center gap-4 mb-3">
                        <button type="button" onclick="toggleAllProducts(true)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Pilih Semua</button>
                        <button type="button" onclick="toggleAllProducts(false)" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Hapus Semua</button>
                    </div>
                    <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-64 overflow-y-auto">
                        @foreach($products as $product)
                        <label class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                {{ is_array(old('product_ids')) && in_array($product->id, old('product_ids')) ? 'checked' : '' }}
                                class="product-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                <div class="text-xs text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }} — Affiliator: {{ $product->commission_percent }}% / {{ $product->commission_percent_non_owner ?? $product->commission_percent }}% — Upline: {{ $product->upline_percent }}% / {{ $product->upline_percent_non_owner ?? $product->upline_percent }}%</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('product_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('product_ids.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="border border-indigo-200 rounded-lg p-4 bg-indigo-50">
                    <h3 class="text-sm font-semibold text-indigo-900 mb-1">Komisi Khusus</h3>
                    <p class="text-xs text-indigo-600 mb-4">Tarif ini akan menggantikan tarif default produk untuk member yang dipilih. Tarif yang sama akan diterapkan ke semua produk yang dicentang. Kosongkan jika ingin tetap menggunakan tarif default.</p>

                    <div class="space-y-4">
                        <div>
                            <label for="commission_percent" class="block text-sm font-medium text-gray-700 mb-1">Komisi Affiliator (%)</label>
                            <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent') }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Persentase dari harga produk yang diterima member ini sebagai komisi affiliator.</p>
                            @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="upline_percent" class="block text-sm font-medium text-gray-700 mb-1">Bonus Upline (%)</label>
                            <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent') }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Persentase bonus yang diterima upline dari member ini saat ada penjualan dari downline-nya.</p>
                            @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Simpan</button>
                <a href="{{ route('admin.member-commissions.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleAllProducts(checked) {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checked);
    }
</script>
@endsection
