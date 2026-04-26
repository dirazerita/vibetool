@extends('layouts.public')
@section('title', 'PRODIG - Marketplace Produk Digital')

@section('content')
<div class="bg-indigo-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-4">Marketplace Produk Digital</h1>
        <p class="text-xl text-indigo-100 mb-8">Temukan produk digital berkualitas dan dapatkan komisi sebagai affiliator!</p>
        @guest
            <a href="{{ route('register') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-indigo-50 inline-block">Daftar Sekarang</a>
        @endguest
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-8">Produk Digital</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($products as $product)
        @php
            $thumbUrl = null;
            if ($product->thumbnail) {
                $thumbUrl = asset('storage/' . $product->thumbnail);
            } elseif ($product->landingPage && $product->landingPage->hero_image) {
                $thumbUrl = asset('storage/' . $product->landingPage->hero_image);
            }
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="h-48 relative">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 flex items-center justify-center p-4">
                        <span class="text-white text-lg font-bold text-center leading-snug drop-shadow-md">{{ $product->title }}</span>
                    </div>
                @endif
            </div>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $product->title }}</h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $product->description }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold text-indigo-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <a href="{{ route('product.show', $product->slug) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">Detail</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
