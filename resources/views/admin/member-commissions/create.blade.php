@extends('layouts.admin')
@section('title', 'Tambah Komisi Khusus')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Tambah Komisi Khusus</h1>

@if(session('error'))
    <div class="dk-alert-error">{{ session('error') }}</div>
@endif

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('admin.member-commissions.store') }}">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="user_id" class="dk-label">Pilih Member</label>
                    <select name="user_id" id="user_id" class="w-full dk-input" required>
                        <option value="">-- Pilih Member --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('user_id') == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="dk-label mb-2">Pilih Produk</label>
                    <p class="text-xs dk-text-muted mb-3">Centang satu atau beberapa produk yang ingin di-set komisi khususnya.</p>
                    <div class="flex items-center gap-4 mb-3">
                        <button type="button" onclick="toggleAllProducts(true)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Pilih Semua</button>
                        <button type="button" onclick="toggleAllProducts(false)" class="text-xs dk-text-muted hover:dk-text font-medium">Hapus Semua</button>
                    </div>
                    <div class="rounded-lg max-h-64 overflow-y-auto" style="border:1px solid #2d3a4a">
                        @foreach($products as $product)
                        <label class="flex items-center px-4 py-3 hover:" style="background:#151e2d cursor-pointer">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                {{ is_array(old('product_ids')) && in_array($product->id, old('product_ids')) ? 'checked' : '' }}
                                class="product-checkbox rounded" style="background:#151e2d;border:1px solid #2d3a4a">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium dk-heading">{{ $product->title }}</div>
                                <div class="dk-text-muted" style="font-size:12px">Rp {{ number_format($product->price, 0, ',', '.') }} — Affiliator: {{ $product->commission_percent }}% / {{ $product->commission_percent_non_owner ?? $product->commission_percent }}% — Upline: {{ $product->upline_percent }}% / {{ $product->upline_percent_non_owner ?? $product->upline_percent }}%</div>
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
                            <label for="commission_percent" class="dk-label">Komisi Affiliator (%)</label>
                            <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent') }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full dk-input">
                            <p class="text-xs mt-1 dk-text-muted">Persentase dari harga produk yang diterima member ini sebagai komisi affiliator.</p>
                            @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="upline_percent" class="dk-label">Bonus Upline (%)</label>
                            <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent') }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full dk-input">
                            <p class="text-xs mt-1 dk-text-muted">Persentase bonus yang diterima upline dari member ini saat ada penjualan dari downline-nya.</p>
                            @error('upline_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Simpan</button>
                <a href="{{ route('admin.member-commissions.index') }}" class="dk-btn dk-btn-outline">Batal</a>
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
