@extends('layouts.public')
@section('title', $product->title . ' - PRODIG')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 h-64 flex items-center justify-center">
            <svg class="w-24 h-24 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->title }}</h1>
            <p class="text-gray-600 mb-6 leading-relaxed">{{ $product->description }}</p>

            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-center">
                <div class="text-sm text-gray-500">Harga</div>
                <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
            </div>

            <div class="flex gap-4">
                @auth
                    <a href="{{ route('checkout', $product->slug) }}" class="flex-1 bg-indigo-600 text-white text-center py-3 rounded-lg hover:bg-indigo-700 font-bold text-lg">Beli Sekarang</a>
                @else
                    <a href="{{ route('register') }}" class="flex-1 bg-indigo-600 text-white text-center py-3 rounded-lg hover:bg-indigo-700 font-bold text-lg">Beli Sekarang</a>
                @endauth
                <a href="{{ route('home') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
