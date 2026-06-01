@extends('layouts.public')
@section('title', 'VibeTool.Id - Marketplace Produk Digital')

@section('content')
<div class="vt-hero" style="background: linear-gradient(135deg, #1e1b4b, #312e81, #4c1d95); padding: 64px 0;">
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #ffffff; margin-bottom: 16px;">Marketplace Produk Digital</h1>
        <p style="font-size: 1.25rem; color: #c7d2fe; margin-bottom: 32px;">Temukan produk digital berkualitas dan dapatkan komisi sebagai affiliator!</p>
        @guest
            <a href="{{ route('register') }}" style="display: inline-block; background: linear-gradient(135deg, #6366f1, #a78bfa); color: #ffffff; padding: 12px 32px; border-radius: 10px; font-weight: 700; font-size: 1.125rem; text-decoration: none; box-shadow: 0 4px 15px rgba(99,102,241,0.4);">Daftar Sekarang</a>
        @endguest
    </div>
</div>

<div class="vt-section-pad" style="max-width: 80rem; margin: 0 auto; padding: 48px 1rem;">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0; margin-bottom: 32px;">Produk Digital</h2>
    <div class="vt-product-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px;">
        @foreach($products as $product)
        @php
            $thumbUrl = null;
            if ($product->thumbnail) {
                $thumbUrl = asset('storage/' . $product->thumbnail);
            } elseif ($product->landingPage && $product->landingPage->hero_image) {
                $thumbUrl = asset('storage/' . $product->landingPage->hero_image);
            }
        @endphp
        <div style="background-color: #1a2332; border: 1px solid #2d3a4a; border-radius: 12px; overflow: hidden; transition: box-shadow 0.2s;">
            <div style="height: 192px; position: relative;">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4f46e5, #7c3aed, #4338ca); display: flex; align-items: center; justify-content: center; padding: 16px;">
                        <span style="color: #ffffff; font-size: 1.125rem; font-weight: 700; text-align: center; line-height: 1.4;">{{ $product->title }}</span>
                    </div>
                @endif
            </div>
            <div style="padding: 24px;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #e2e8f0; margin-bottom: 8px;">{{ $product->title }}</h3>
                <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 16px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $product->description }}</p>
                @php
                    $homeStartingPrice = $product->startingPrice();
                    $homeHasPkg = $product->hasPackages();
                    $homeCompareAt = ! $homeHasPkg && $product->compare_at_price !== null && (float) $product->compare_at_price > (float) $product->price
                        ? (float) $product->compare_at_price
                        : null;
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    @if($product->isFree())
                        <span style="font-size: 1.5rem; font-weight: 700; color: #10b981;">GRATIS</span>
                    @else
                        <div style="line-height: 1.1;">
                            @if($homeCompareAt !== null)
                                <div style="font-size: 0.875rem; color: #64748b; text-decoration: line-through;">Rp {{ number_format($homeCompareAt, 0, ',', '.') }}</div>
                            @endif
                            <span style="font-size: 1.5rem; font-weight: 700; color: #818cf8;">
                                @if($homeHasPkg)<span style="font-size: 0.75rem; font-weight: 400; color: #94a3b8;">Mulai </span>@endif
                                Rp {{ number_format($homeStartingPrice, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    <a href="{{ route('product.show', $product->slug) }}" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 500; text-decoration: none;">Detail</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
