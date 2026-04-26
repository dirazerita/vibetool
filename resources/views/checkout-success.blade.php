@extends('layouts.public')
@section('title', 'Pembayaran Berhasil - PRODIG')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Terima Kasih!</h1>
        <p class="text-gray-600 mb-6">Pesanan Anda sedang diproses. Anda akan menerima link download setelah pembayaran dikonfirmasi.</p>

        @if($order->isPaid() && $order->download_token)
            <a href="{{ route('download', $order->download_token) }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-bold">
                Download Produk
            </a>
        @else
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                Menunggu konfirmasi pembayaran. Silakan cek email Anda untuk link download.
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
