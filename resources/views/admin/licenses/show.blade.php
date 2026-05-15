@extends('layouts.admin')
@section('title', 'Lisensi: ' . $product->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.licenses') }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Kembali ke daftar produk</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Lisensi: {{ $product->title }}</h1>
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Total Lisensi</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($licenses->total()) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Tersedia</div>
        <div class="text-2xl font-bold {{ $availableCount > 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ number_format($availableCount) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs text-gray-500 uppercase">Order Belum Dapat Lisensi</div>
        <div class="text-2xl font-bold {{ $pendingOrders->count() > 0 ? 'text-yellow-600' : 'text-gray-900' }} mt-1">{{ number_format($pendingOrders->count()) }}</div>
    </div>
</div>

@if($pendingOrders->count() > 0)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-semibold text-yellow-900 mb-2">Order paid yang belum mendapat lisensi</h2>
    <p class="text-xs text-yellow-800 mb-3">Tambah lisensi baru di bawah, lalu klik "Berikan Lisensi" untuk mengalokasikan ke member.</p>
    <div class="space-y-2">
        @foreach($pendingOrders as $order)
        <div class="flex items-center justify-between bg-white rounded-lg border border-yellow-200 p-3">
            <div class="text-sm">
                <div class="font-medium text-gray-900">Order #{{ $order->id }}</div>
                <div class="text-xs text-gray-600">{{ $order->user?->name ?? '—' }} · {{ $order->user?->email ?? '—' }} · {{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
            <form method="POST" action="{{ route('admin.licenses.assign-order', $order) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg hover:bg-yellow-600 {{ $availableCount === 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $availableCount === 0 ? 'disabled' : '' }}>
                    Berikan Lisensi
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah Lisensi</h2>
    <form method="POST" action="{{ route('admin.licenses.store', $product) }}">
        @csrf
        <div class="mb-4">
            <label for="keys" class="block text-sm font-medium text-gray-700 mb-1">Kunci Lisensi (1 per baris, paste bulk)</label>
            <textarea name="keys" id="keys" rows="6" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm" placeholder="LICENSE-XXXX-YYYY-ZZZZ&#10;LICENSE-AAAA-BBBB-CCCC&#10;...">{{ old('keys') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Setiap baris akan dianggap satu kunci lisensi. Duplikat dilewati otomatis.</p>
        </div>
        <div class="mb-4">
            <label for="extra_info" class="block text-sm font-medium text-gray-700 mb-1">Catatan / Instruksi Aktivasi (opsional)</label>
            <textarea name="extra_info" id="extra_info" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Misal: Untuk aktivasi, buka www.example.com/activate dan masukkan kunci di atas.">{{ old('extra_info') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Catatan ini akan ditampilkan ke member bersama dengan kunci lisensi. Sama untuk semua lisensi yang ditambah di batch ini.</p>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Tambah Lisensi</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Daftar Lisensi</h2>
    </div>
    @if($licenses->total() === 0)
        <div class="p-10 text-center text-gray-500 text-sm">Belum ada lisensi untuk produk ini.</div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kunci</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dialokasikan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($licenses as $license)
                <tr>
                    <td class="px-6 py-3 whitespace-nowrap text-sm font-mono text-gray-900">{{ $license->key }}</td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                        @if($license->isAssigned())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Terpakai</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Tersedia</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                        @if($license->user)
                            <div class="font-medium">{{ $license->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $license->user->email }}</div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">{{ $license->order_id ? '#' . $license->order_id : '—' }}</td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                        {{ $license->assigned_at ? $license->assigned_at->format('d M Y H:i') : '—' }}
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-sm">
                        @if(!$license->isAssigned())
                            <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" onsubmit="return confirm('Hapus lisensi ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">Hapus</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">Tidak bisa dihapus</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">{{ $licenses->links() }}</div>
    @endif
</div>
@endsection
