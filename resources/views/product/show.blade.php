@extends('layouts.public')
@section('title', $product->title . ' - PRODIG')

@section('content')
<div style="max-width: 56rem; margin: 0 auto; padding: 48px 1rem;">
    <div style="background-color: #1a2332; border: 1px solid #2d3a4a; border-radius: 12px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4f46e5, #7c3aed); height: 256px; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 96px; height: 96px; color: rgba(255,255,255,0.8);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
        <div style="padding: 32px;">
            <h1 style="font-size: 1.875rem; font-weight: 700; color: #e2e8f0; margin-bottom: 16px;">{{ $product->title }}</h1>
            <p style="color: #94a3b8; margin-bottom: 24px; line-height: 1.75;">{{ $product->description }}</p>

            <div style="background-color: #151e2d; border-radius: 8px; padding: 24px; margin-bottom: 24px; text-align: center;">
                <div style="font-size: 0.875rem; color: #94a3b8;">Harga</div>
                @if($product->isFree())
                    <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">GRATIS</div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">Klaim langsung — login pakai akun PRODIG kamu</div>
                @else
                    <div style="font-size: 1.5rem; font-weight: 700; color: #818cf8;">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                @endif
            </div>

            @php
                $registerUrl = route('register');
                if (session('ref_code')) {
                    $registerUrl .= '?ref=' . urlencode(session('ref_code'));
                }
                $ctaLabel = $product->isFree() ? 'Dapatkan Gratis' : 'Beli Sekarang';
                $ctaBg = $product->isFree() ? '#10b981' : 'linear-gradient(135deg, #4f46e5, #7c3aed)';
            @endphp
            <div style="display: flex; gap: 16px;">
                @auth
                    @if($product->isFree())
                        <form method="POST" action="{{ route('free.claim', $product->slug) }}" style="flex: 1;">
                            @csrf
                            <button type="submit" style="width: 100%; background: {{ $ctaBg }}; color: #ffffff; text-align: center; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 1.125rem; border: 0; cursor: pointer;">{{ $ctaLabel }}</button>
                        </form>
                    @else
                        <a href="{{ route('checkout', $product->slug) }}" style="flex: 1; background: {{ $ctaBg }}; color: #ffffff; text-align: center; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 1.125rem; text-decoration: none;">{{ $ctaLabel }}</a>
                    @endif
                @else
                    <a href="{{ $registerUrl }}" style="flex: 1; background: {{ $ctaBg }}; color: #ffffff; text-align: center; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 1.125rem; text-decoration: none;">{{ $ctaLabel }}</a>
                @endauth
                <a href="{{ route('home') }}" style="padding: 12px 24px; border: 1px solid #2d3a4a; border-radius: 8px; color: #cbd5e1; font-weight: 500; text-decoration: none;">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
