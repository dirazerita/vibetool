@extends('layouts.admin')
@section('title', 'Kelola Produk')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kelola Produk</h1>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Tambah Produk</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($products as $product)
        @php
            $lp = $product->landingPage;
            $cardImage = null;
            if ($product->thumbnail) {
                $cardImage = asset('storage/' . $product->thumbnail);
            } elseif ($lp && $lp->hero_image) {
                $cardImage = asset('storage/' . $lp->hero_image);
            }
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200">
            {{-- Thumbnail --}}
            <div class="h-44 bg-gray-100 relative">
                @if($cardImage)
                    <img src="{{ $cardImage }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 flex items-center justify-center p-4">
                        <span class="text-white text-base font-bold text-center leading-snug drop-shadow-md">{{ $product->title }}</span>
                    </div>
                @endif

                {{-- Badge status & landing page --}}
                <div class="absolute top-2 left-2 flex gap-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm {{ $product->is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @if($lp)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm {{ $lp->is_published ? 'bg-blue-500 text-white' : 'bg-gray-400 text-white' }}">
                            {{ $lp->is_published ? 'Published' : 'Draft' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4">
                <h3 class="text-lg font-bold text-gray-900 truncate">{{ $product->title }}</h3>
                <p class="text-xs text-gray-400 mb-3">{{ $product->slug }}</p>

                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-bold text-green-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500">Komisi: {{ $product->commission_percent }}% / {{ $product->upline_percent }}%</span>
                </div>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-indigo-600 hover:bg-indigo-50 rounded text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <a href="{{ route('admin.products.landing-page', $product) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-green-600 hover:bg-green-50 rounded text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Landing
                    </a>
                    <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-gray-500 hover:bg-gray-50 rounded text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Preview
                    </a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded text-xs font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6">{{ $products->links() }}</div>
@endsection
