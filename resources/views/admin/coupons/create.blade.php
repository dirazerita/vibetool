@extends('layouts.admin')
@section('title', 'Tambah Kupon')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Tambah Kupon</h1>

<div class="max-w-2xl">
    <div class="dk-card" style="padding:24px;">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="code" class="dk-label">Kode Kupon</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" id="code" value="{{ old('code') }}" class="flex-1 dk-input uppercase" required>
                        <button type="button" id="generate-code" class="dk-btn dk-btn-outline" style="font-size:13px;white-space:nowrap">Generate</button>
                    </div>
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="dk-label">Nama / Deskripsi</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full dk-input" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="dk-grid-2 gap-4" style="display:grid;grid-template-columns:repeat(2,1fr)">
                    <div>
                        <label for="discount_type" class="dk-label">Tipe Diskon</label>
                        <select name="discount_type" id="discount_type" class="w-full dk-input" required>
                            <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Persen (%)</option>
                            <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                        @error('discount_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="discount_value" class="dk-label">Nilai Diskon</label>
                        <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value') }}" step="0.01" class="w-full dk-input" required>
                        @error('discount_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="dk-grid-2 gap-4" style="display:grid;grid-template-columns:repeat(2,1fr)">
                    <div>
                        <label for="min_purchase" class="dk-label">Minimal Pembelian (Rp)</label>
                        <input type="number" name="min_purchase" id="min_purchase" value="{{ old('min_purchase', 0) }}" step="0.01" class="w-full dk-input">
                        @error('min_purchase') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="max_uses" class="dk-label">Maksimal Penggunaan</label>
                        <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses') }}" min="1" class="w-full dk-input" placeholder="Kosongkan = unlimited">
                        @error('max_uses') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="expired_at" class="dk-label">Tanggal Expired</label>
                    <input type="datetime-local" name="expired_at" id="expired_at" value="{{ old('expired_at') }}" class="w-full dk-input">
                    @error('expired_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="members" class="dk-label">Assign ke Member (kosongkan = semua member)</label>
                    <div x-data="memberPicker({{ json_encode($members->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'email' => $m->email])) }}, {{ json_encode(old('members', [])) }})" class="relative">
                        {{-- Input pencarian --}}
                        <input type="text" x-model="query" @input="filter()" placeholder="Cari nama atau email member..."
                               class="w-full dk-input mb-2 text-sm" style="padding:8px 12px;">
                        {{-- Selected tags --}}
                        <div class="flex flex-wrap gap-1 mb-2" x-show="selected.length > 0">
                            <template x-for="(id, idx) in selected" :key="id">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs" style="background:rgba(79,70,229,0.2);color:#a5b4fc;border:1px solid rgba(79,70,229,0.3)">
                                    <span x-text="getName(id)"></span>
                                    <button type="button" @click="remove(idx)" class="hover:text-white" style="font-size:14px;line-height:1;">&times;</button>
                                </span>
                            </template>
                        </div>
                        {{-- Dropdown hasil --}}
                        <div x-show="results.length > 0 && query.length > 0"
                             class="absolute z-50 w-full rounded-md max-h-44 overflow-y-auto"
                             style="background:#0f1623;border:1px solid #2d3a4a;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                            <template x-for="m in results" :key="m.id">
                                <label class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-800" style="color:#e2e8f0">
                                    <input type="checkbox" :checked="isSelected(m.id)" @change="toggle(m.id)" class="rounded" style="accent-color:#818cf8">
                                    <span x-text="m.name"></span>
                                    <span class="text-xs dk-text-muted" x-text="'('+m.email+')'"></span>
                                </label>
                            </template>
                        </div>
                        <p class="text-xs mt-1 dk-text-muted" x-show="query.length > 0 && results.length === 0" x-cloak>Tidak ada member yang cocok.</p>
                        {{-- Hidden select untuk submit --}}
                        <select name="members[]" id="members" multiple class="hidden" x-ref="select">
                            <template x-for="id in selected" :key="id">
                                <option :value="id" selected></option>
                            </template>
                        </select>
                    </div>
                    @error('members') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="products" class="dk-label">Assign ke Produk (kosongkan = semua produk)</label>
                    <select name="products[]" id="products" multiple class="w-full dk-input" style="min-height: 120px;">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ in_array($product->id, old('products', [])) ? 'selected' : '' }}>{{ $product->title }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs mt-1 dk-text-muted">Tahan Ctrl/Cmd untuk memilih beberapa produk</p>
                    @error('products') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="dk-checkbox">
                        <span class="ml-2 text-sm dk-text">Kupon Aktif</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button type="submit" class="dk-btn dk-btn-primary">Simpan Kupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="dk-btn dk-btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('generate-code').addEventListener('click', function() {
    fetch('{{ route("admin.coupons.generate-code") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('code').value = data.code;
    });
});
</script>

<script>
    function memberPicker(allMembers, initialSelected) {
        return {
            all: allMembers,
            selected: initialSelected.filter(id => id !== null && id !== undefined && id !== ''),
            query: '',
            results: [],
            filter() {
                const q = this.query.toLowerCase().trim();
                if (!q) { this.results = []; return; }
                this.results = this.all.filter(m =>
                    m.name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q)
                ).slice(0, 30);
            },
            getName(id) {
                const m = this.all.find(x => x.id == id);
                return m ? m.name : '?';
            },
            isSelected(id) { return this.selected.indexOf(id) !== -1; },
            toggle(id) {
                if (this.isSelected(id)) {
                    this.selected = this.selected.filter(x => x !== id);
                } else { this.selected.push(id); }
            },
            remove(idx) { this.selected.splice(idx, 1); },
        };
    }
</script>
@endsection
