@extends('layouts.admin')
@section('title', 'Lisensi: ' . $product->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.licenses') }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Kembali ke daftar produk</a>
    <h1 class="text-2xl font-bold dk-heading mt-2">Lisensi: {{ $product->title }}</h1>
</div>

@if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
        @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Total Lisensi</div>
        <div class="text-2xl font-bold dk-heading mt-1">{{ number_format($licenses->total()) }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Tersedia</div>
        <div class="text-2xl font-bold {{ $availableCount > 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ number_format($availableCount) }}</div>
    </div>
    <div class="dk-card p-5">
        <div class="text-xs dk-text-muted uppercase">Order Belum Dapat Lisensi</div>
        <div class="text-2xl font-bold {{ $pendingOrders->count() > 0 ? 'text-yellow-600' : 'dk-heading' }} mt-1">{{ number_format($pendingOrders->count()) }}</div>
    </div>
</div>

@if($pendingOrders->count() > 0)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-semibold text-yellow-900 mb-2">Order paid yang belum mendapat lisensi</h2>
    <p class="text-xs text-yellow-800 mb-3">Klik "Generate Lisensi" untuk membuat kunci lisensi otomatis dan mengalokasikan ke member.</p>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="flex items-center justify-between dk-card rounded-lg border border-yellow-200 p-3">
            <div class="text-sm">
                <div class="font-medium dk-heading">Order #{{ $order->id }}</div>
                <div class="text-xs dk-text">{{ $order->user?->name ?? '—' }} · {{ $order->user?->email ?? '—' }} · {{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
            <form method="POST" action="{{ route('admin.licenses.assign-order', $order) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg hover:bg-yellow-600">
                    Generate Lisensi
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="dk-card" style="padding:24px mb-6">
    <h2 class="text-lg font-semibold dk-heading mb-4">Tambah Lisensi</h2>
    <form method="POST" action="{{ route('admin.licenses.store', $product) }}">
        @csrf
        <div class="mb-4">
            <label for="keys" class="dk-label">Kunci Lisensi (1 per baris, paste bulk)</label>
            <textarea name="keys" id="keys" rows="6" class="w-full dk-input font-mono text-sm" placeholder="LICENSE-XXXX-YYYY-ZZZZ&#10;LICENSE-AAAA-BBBB-CCCC&#10;...">{{ old('keys') }}</textarea>
            <p class="text-xs mt-1 dk-text-muted">Setiap baris akan dianggap satu kunci lisensi. Duplikat dilewati otomatis.</p>
        </div>
        <div class="mb-4">
            <label for="extra_info" class="dk-label">Catatan / Instruksi Aktivasi (opsional)</label>
            <textarea name="extra_info" id="extra_info" rows="3" class="w-full dk-input text-sm" placeholder="Misal: Untuk aktivasi, buka www.example.com/activate dan masukkan kunci di atas.">{{ old('extra_info') }}</textarea>
            <p class="text-xs mt-1 dk-text-muted">Catatan ini akan ditampilkan ke member bersama dengan kunci lisensi. Sama untuk semua lisensi yang ditambah di batch ini.</p>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Tambah Lisensi</button>
    </form>
</div>

<div class="dk-table">
    <div class="px-6 py-4 " style="border-bottom:1px solid #1e2b3d">
        <h2 class="text-lg font-semibold dk-heading">Daftar Lisensi</h2>
    </div>
    @if($licenses->total() === 0)
        <div class="p-10 text-center dk-text-muted text-sm">Belum ada lisensi untuk produk ini.</div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Kunci</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Dialokasikan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Masa Berlaku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color:#94a3b8">Aksi</th>
                </tr>
            </thead>
            <tbody class="dk-card ">
                @foreach($licenses as $license)
                <tr>
                    <td class="px-6 py-3 whitespace-nowrap text-sm font-mono dk-heading">{{ $license->key }}</td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                        @if($license->isAssigned())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Terpakai</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Tersedia</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm dk-text">
                        @if($license->user)
                            <div class="font-medium">{{ $license->user->name }}</div>
                            <div class="dk-text-muted" style="font-size:12px">{{ $license->user->email }}</div>
                        @else
                            <span style="color:#4a5568">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm dk-text">{{ $license->order_id ? '#' . $license->order_id : '—' }}</td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm dk-text">
                        {{ $license->assigned_at ? $license->assigned_at->format('d M Y H:i') : '—' }}
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                        @if($license->isAssigned())
                            @if($license->isLifetime())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Lifetime</span>
                            @elseif($license->isExpired())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5">Kedaluwarsa</span>
                                <div class="text-xs dk-text-muted mt-0.5">{{ $license->expires_at->format('d M Y H:i') }}</div>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Aktif</span>
                                <div class="text-xs dk-text-muted mt-0.5">s/d {{ $license->expires_at->format('d M Y H:i') }}</div>
                            @endif
                        @else
                            <span style="color:#4a5568">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            @if($license->isAssigned())
                                <button type="button" onclick="openEditModal({{ $license->id }}, '{{ $license->expires_at ? $license->expires_at->format('Y-m-d\TH:i') : '' }}', {{ $license->isLifetime() ? 'true' : 'false' }})" class="text-indigo-600 hover:text-indigo-700 text-xs font-medium">Edit</button>
                            @endif
                            <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" onsubmit="return confirm('Hapus lisensi ini? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 dk-divider">{{ $licenses->links() }}</div>
    @endif
</div>
{{-- Modal Edit Masa Berlaku --}}
<div id="edit-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="dk-card rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold dk-heading mb-4">Edit Masa Berlaku Lisensi</h3>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="dk-label mb-2">Pilih Durasi</label>
                <select name="duration_preset" id="edit-preset" class="w-full dk-input" onchange="toggleCustomDate()">
                    <option value="1_month">1 Bulan (dari tanggal alokasi)</option>
                    <option value="6_months">6 Bulan (dari tanggal alokasi)</option>
                    <option value="1_year">1 Tahun (dari tanggal alokasi)</option>
                    <option value="lifetime">Lifetime</option>
                    <option value="custom">Tanggal Custom</option>
                </select>
            </div>
            <div id="custom-date-section" class="mb-4 hidden">
                <label class="dk-label">Tanggal Kedaluwarsa</label>
                <input type="datetime-local" name="expires_at" id="edit-expires-at" class="w-full dk-input">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border dk-input rounded-lg dk-text hover:" style="background:#151e2d text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(licenseId, expiresAt, isLifetime) {
        const modal = document.getElementById('edit-modal');
        const form = document.getElementById('edit-form');
        const preset = document.getElementById('edit-preset');
        const expiresInput = document.getElementById('edit-expires-at');

        form.action = '/admin/licenses/' + licenseId;

        if (isLifetime) {
            preset.value = 'lifetime';
        } else if (expiresAt) {
            preset.value = 'custom';
            expiresInput.value = expiresAt;
        }

        toggleCustomDate();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleCustomDate() {
        const preset = document.getElementById('edit-preset').value;
        const section = document.getElementById('custom-date-section');
        section.classList.toggle('hidden', preset !== 'custom');
    }

    document.getElementById('edit-modal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
@endsection
