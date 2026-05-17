@extends('layouts.public')
@section('title', 'Instruksi Pembayaran - PRODIG')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ showUpload: false }">
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-6">
            @if($order->product && $order->product->thumbnail)
                <img src="{{ asset('storage/' . $order->product->thumbnail) }}" alt="{{ $order->product->title }}" class="w-32 h-32 object-cover rounded-xl mx-auto mb-4 border border-gray-200 shadow-sm">
            @else
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            @endif
            <h1 class="text-2xl font-bold text-gray-900">Instruksi Pembayaran</h1>
            <p class="text-gray-500 text-sm mt-1">Pesanan #{{ $order->id }} &middot; {{ $order->product->title ?? 'Produk' }}</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-5 mb-6">
            <div class="text-sm text-gray-600 mb-1">Total yang harus ditransfer</div>
            <div class="text-3xl font-bold text-indigo-700">Rp {{ number_format($order->amount, 0, ',', '.') }}</div>
        </div>

        @if($adminWhatsappLink)
            <a href="{{ $adminWhatsappLink }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-3 w-full px-6 py-4 mb-6 bg-green-500 text-white rounded-xl hover:bg-green-600 font-bold text-base shadow-md transition-all hover:shadow-lg ring-2 ring-green-200 hover:ring-green-300">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.595 5.392l-.999 3.648 3.893-1.022zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                Hubungi Admin via WhatsApp
            </a>
        @endif

        <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 mb-6">
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-gray-500">Bank</span>
                <span class="font-medium text-gray-900">{{ $bankInfo['bank_name'] ?: '-' }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-gray-500">Nomor Rekening</span>
                <span class="font-mono font-medium text-gray-900" id="bank-account">{{ $bankInfo['bank_account'] ?: '-' }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-gray-500">Atas Nama</span>
                <span class="font-medium text-gray-900">{{ $bankInfo['bank_holder'] ?: '-' }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-gray-500">ID Pesanan</span>
                <span class="font-mono font-medium text-gray-900">#{{ $order->id }}</span>
            </div>
        </div>

        @if($bankInfo['note'])
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800 whitespace-pre-line">{{ $bankInfo['note'] }}</div>
        @endif

        @if($order->status === 'paid')
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6 text-sm">
                <strong>Pembayaran sudah dikonfirmasi.</strong> Silakan akses produk dari menu <a href="{{ route('dashboard.purchases') }}" class="underline">Pembelian Saya</a>.
            </div>
        @elseif($order->payment_proof)
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 mb-6 text-sm">
                <strong>Bukti transfer sudah diupload.</strong> Menunggu konfirmasi admin. Pesanan akan otomatis aktif setelah admin verifikasi.
            </div>

            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-2">Bukti transfer terakhir:</p>
                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="inline-block">
                    <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti transfer" class="max-h-64 rounded-lg border border-gray-200">
                </a>
            </div>
        @endif

        @if($order->status === 'pending')
            <div x-show="!showUpload">
                <button type="button" @click="showUpload = true" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-medium">
                    {{ $order->payment_proof ? 'Upload Ulang Bukti Transfer' : 'Saya Sudah Transfer' }}
                </button>
            </div>

            <form x-show="showUpload" x-transition method="POST" action="{{ route('checkout.manual.proof', $order->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="proof" class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                    <input type="file" name="proof" id="proof" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
                    @error('proof') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-6 py-2.5 rounded-lg hover:bg-indigo-700 font-medium">Kirim Bukti Transfer</button>
                    <button type="button" @click="showUpload = false" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
                </div>
            </form>

            <form method="POST" action="{{ route('checkout.manual.cancel', $order->id) }}" class="mt-3" onsubmit="return confirm('Batalkan pesanan #{{ $order->id }}? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50 font-medium text-sm">
                    Batalkan Pesanan
                </button>
            </form>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('dashboard.purchases') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Lihat semua pembelian saya</a>
        </div>
    </div>
</div>
@endsection
