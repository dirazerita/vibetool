@extends('layouts.admin')
@section('title', 'Edit Komisi Khusus')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Komisi Khusus</h1>

@if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.member-commissions.update', $memberCommission) }}">
            @csrf @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Member</label>
                    <select name="user_id" id="user_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">-- Pilih Member --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('user_id', $memberCommission->user_id) == $member->id ? 'selected' : '' }}>{{ $member->name }} ({{ $member->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk</label>
                    <select name="product_id" id="product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $memberCommission->product_id) == $product->id ? 'selected' : '' }}
                                data-commission="{{ $product->commission_percent }}"
                                data-commission-non="{{ $product->commission_percent_non_owner ?? $product->commission_percent }}"
                                data-upline="{{ $product->upline_percent }}"
                                data-upline-non="{{ $product->upline_percent_non_owner ?? $product->upline_percent }}">
                                {{ $product->title }} — Rp {{ number_format($product->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="default-rates" class="{{ $memberCommission->product_id ? '' : 'hidden' }} border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Tarif Default Produk</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Affiliator (sudah beli):</span>
                            <span id="def-commission" class="font-medium text-gray-900">{{ $memberCommission->product->commission_percent ?? '' }}%</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Affiliator (belum beli):</span>
                            <span id="def-commission-non" class="font-medium text-gray-900">{{ $memberCommission->product->commission_percent_non_owner ?? $memberCommission->product->commission_percent ?? '' }}%</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Upline (sudah beli):</span>
                            <span id="def-upline" class="font-medium text-gray-900">{{ $memberCommission->product->upline_percent ?? '' }}%</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Upline (belum beli):</span>
                            <span id="def-upline-non" class="font-medium text-gray-900">{{ $memberCommission->product->upline_percent_non_owner ?? $memberCommission->product->upline_percent ?? '' }}%</span>
                        </div>
                    </div>
                </div>

                <div class="border border-indigo-200 rounded-lg p-4 bg-indigo-50">
                    <h3 class="text-sm font-semibold text-indigo-900 mb-1">Komisi Khusus</h3>
                    <p class="text-xs text-indigo-600 mb-4">Tarif ini akan menggantikan tarif default produk untuk member yang dipilih. Kosongkan jika ingin tetap menggunakan tarif default.</p>

                    <div class="space-y-4">
                        <div>
                            <label for="commission_percent" class="block text-sm font-medium text-gray-700 mb-1">Komisi Affiliator (%)</label>
                            <input type="number" name="commission_percent" id="commission_percent" value="{{ old('commission_percent', $memberCommission->commission_percent) }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Persentase dari harga produk yang diterima member ini sebagai komisi affiliator.</p>
                            @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="upline_percent" class="block text-sm font-medium text-gray-700 mb-1">Bonus Upline (%)</label>
                            <input type="number" name="upline_percent" id="upline_percent" value="{{ old('upline_percent', $memberCommission->upline_percent) }}" step="0.01" min="0" max="100" placeholder="Kosongkan = pakai default" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
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
    document.getElementById('product_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const section = document.getElementById('default-rates');
        if (this.value) {
            document.getElementById('def-commission').textContent = selected.dataset.commission + '%';
            document.getElementById('def-commission-non').textContent = selected.dataset.commissionNon + '%';
            document.getElementById('def-upline').textContent = selected.dataset.upline + '%';
            document.getElementById('def-upline-non').textContent = selected.dataset.uplineNon + '%';
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    });
</script>
@endsection
