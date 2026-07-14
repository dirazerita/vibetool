@extends('layouts.public')
@section('title', 'VibeTool.Id - Marketplace Produk Digital')

@section('content')
<style>
    /* Hero */
    .vt-hero { position: relative; padding: 88px 0 96px; overflow: hidden; }
    .vt-hero-orb { position: absolute; border-radius: 50%; pointer-events: none; }
    .vt-hero-orb-1 { width: 420px; height: 420px; top: -160px; left: -120px; background: radial-gradient(circle, rgba(99,102,241,0.25), transparent 65%); animation: vtFloat 9s ease-in-out infinite; }
    .vt-hero-orb-2 { width: 360px; height: 360px; bottom: -140px; right: -100px; background: radial-gradient(circle, rgba(139,92,246,0.22), transparent 65%); animation: vtFloat 11s ease-in-out infinite reverse; }
    .vt-hero-orb-3 { width: 220px; height: 220px; top: 30%; right: 18%; background: radial-gradient(circle, rgba(56,189,248,0.14), transparent 65%); animation: vtFloat 13s ease-in-out infinite; }
    @keyframes vtFloat { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-24px) } }

    .vt-hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(99,102,241,0.12); border: 1px solid rgba(129,140,248,0.3);
        color: #c7d2fe; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.02em;
        padding: 6px 16px; border-radius: 9999px; margin-bottom: 24px;
        -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
        animation: vtFadeUp 0.6s ease both;
    }
    .vt-hero-badge .vt-dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 8px rgba(52,211,153,0.9); animation: vtPulse 2s ease-in-out infinite; }
    @keyframes vtPulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }

    .vt-hero h1 {
        font-size: 3rem; font-weight: 800; line-height: 1.15; margin-bottom: 18px; color: #f8fafc;
        animation: vtFadeUp 0.6s ease 0.1s both;
    }
    .vt-hero .vt-grad-text {
        background: linear-gradient(90deg, #818cf8, #c084fc, #38bdf8, #818cf8);
        background-size: 250% auto;
        -webkit-background-clip: text; background-clip: text; color: transparent;
        animation: vtShine 6s linear infinite;
    }
    @keyframes vtShine { to { background-position: 250% center } }

    .vt-hero p { font-size: 1.25rem; color: #a5b4fc; margin-bottom: 36px; animation: vtFadeUp 0.6s ease 0.2s both; }
    .vt-hero-actions { display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap; animation: vtFadeUp 0.6s ease 0.3s both; }
    .vt-btn-hero {
        display: inline-flex; align-items: center; gap: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; padding: 14px 34px; border-radius: 14px; font-weight: 700; font-size: 1.1rem; text-decoration: none;
        box-shadow: 0 8px 30px rgba(99,102,241,0.45), inset 0 1px 0 rgba(255,255,255,0.2);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .vt-btn-hero:hover { transform: translateY(-2px); box-shadow: 0 14px 40px rgba(99,102,241,0.6), inset 0 1px 0 rgba(255,255,255,0.2); filter: brightness(1.07); }
    .vt-btn-ghost {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(148,163,184,0.06); border: 1px solid rgba(148,163,184,0.2);
        color: #cbd5e1; padding: 14px 30px; border-radius: 14px; font-weight: 600; font-size: 1.05rem; text-decoration: none;
        -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
    }
    .vt-btn-ghost:hover { border-color: rgba(129,140,248,0.45); background: rgba(99,102,241,0.1); transform: translateY(-2px); }

    @keyframes vtFadeUp { from { opacity: 0; transform: translateY(18px) } to { opacity: 1; transform: translateY(0) } }

    /* Section header */
    .vt-section-title { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
    .vt-section-title .vt-bar { width: 5px; height: 28px; border-radius: 3px; background: linear-gradient(180deg, #6366f1, #8b5cf6); box-shadow: 0 0 12px rgba(99,102,241,0.5); flex-shrink: 0; }
    .vt-section-title h2 { font-size: 1.6rem; font-weight: 700; color: #f1f5f9; margin: 0; }
    .vt-section-sub { color: #64748b; font-size: 0.95rem; margin: 0 0 32px 19px; }

    /* Product card glass */
    .vt-card {
        background: linear-gradient(160deg, rgba(30,38,66,0.72), rgba(17,23,43,0.66));
        -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
        border: 1px solid rgba(129,140,248,0.14);
        border-radius: 18px; overflow: hidden;
        box-shadow: 0 8px 32px rgba(2,6,23,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
        transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        display: flex; flex-direction: column;
        animation: vtFadeUp 0.55s ease both;
    }
    .vt-card:nth-child(2) { animation-delay: 0.08s; }
    .vt-card:nth-child(3) { animation-delay: 0.16s; }
    .vt-card:nth-child(4) { animation-delay: 0.24s; }
    .vt-card:nth-child(5) { animation-delay: 0.3s; }
    .vt-card:nth-child(6) { animation-delay: 0.36s; }
    .vt-card:hover {
        transform: translateY(-6px);
        border-color: rgba(129,140,248,0.35);
        box-shadow: 0 20px 50px rgba(2,6,23,0.55), 0 0 34px rgba(99,102,241,0.14), inset 0 1px 0 rgba(255,255,255,0.07);
    }
    .vt-card-thumb { aspect-ratio: 1 / 1; position: relative; background: #0d1326; overflow: hidden; }
    .vt-card-thumb img { width: 100%; height: 100%; object-fit: contain; transition: transform 0.45s ease; }
    .vt-card:hover .vt-card-thumb img { transform: scale(1.045); }
    .vt-card-cta {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
        padding: 9px 18px; border-radius: 11px; font-size: 0.875rem; font-weight: 600; text-decoration: none;
        box-shadow: 0 2px 12px rgba(99,102,241,0.3), inset 0 1px 0 rgba(255,255,255,0.16);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        white-space: nowrap;
    }
    .vt-card-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.5), inset 0 1px 0 rgba(255,255,255,0.16); filter: brightness(1.08); }
    .vt-card-cta svg { width: 15px; height: 15px; transition: transform 0.2s ease; }
    .vt-card-cta:hover svg { transform: translateX(3px); }
</style>

<div class="vt-hero">
    <div class="vt-hero-orb vt-hero-orb-1"></div>
    <div class="vt-hero-orb vt-hero-orb-2"></div>
    <div class="vt-hero-orb vt-hero-orb-3"></div>
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; text-align: center; position: relative;">
        <div class="vt-hero-badge"><span class="vt-dot"></span> Tools &amp; Produk Digital Terbaik untuk Bisnismu</div>
        <h1>Marketplace <span class="vt-grad-text">Produk Digital</span></h1>
        <p>Temukan produk digital berkualitas dan dapatkan komisi sebagai affiliator!</p>
        <div class="vt-hero-actions">
            @guest
                <a href="{{ route('register') }}" class="vt-btn-hero">
                    Daftar Sekarang
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </a>
                <a href="#produk" class="vt-btn-ghost">Lihat Produk</a>
            @else
                <a href="#produk" class="vt-btn-hero">
                    Jelajahi Produk
                    <svg style="width:19px;height:19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                </a>
            @endguest
        </div>
    </div>
</div>

<div id="produk" class="vt-section-pad" style="max-width: 80rem; margin: 0 auto; padding: 48px 1rem;">
    <div class="vt-section-title">
        <span class="vt-bar"></span>
        <h2>Tools Produk Marketing Terbaik</h2>
    </div>
    <p class="vt-section-sub">Pilih tool yang tepat, kembangkan bisnismu lebih cepat.</p>
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
        <div class="vt-card">
            <div class="vt-card-thumb">
                @if($thumbUrl)
                    @if($loop->index < 3)
                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" decoding="async" {{ $loop->first ? 'fetchpriority=high' : '' }}>
                    @else
                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" decoding="async">
                    @endif
                @else
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4f46e5, #7c3aed, #4338ca); display: flex; align-items: center; justify-content: center; padding: 16px;">
                        <span style="color: #ffffff; font-size: 1.125rem; font-weight: 700; text-align: center; line-height: 1.4;">{{ $product->title }}</span>
                    </div>
                @endif
            </div>
            <div style="padding: 24px; display: flex; flex-direction: column; flex: 1;">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #f1f5f9; margin-bottom: 8px;">{{ $product->title }}</h3>
                <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 16px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $product->description }}</p>
                @php
                    $homeStartingPrice = $product->startingPrice();
                    $homeHasPkg = $product->hasPackages();
                    $homeCompareAt = ! $homeHasPkg && $product->compare_at_price !== null && (float) $product->compare_at_price > (float) $product->price
                        ? (float) $product->compare_at_price
                        : null;
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto;">
                    @if($product->isFree())
                        <span style="font-size: 1.5rem; font-weight: 800; color: #34d399; text-shadow: 0 0 20px rgba(52,211,153,0.35);">GRATIS</span>
                    @else
                        <div style="line-height: 1.1; min-width: 0;">
                            @if($homeCompareAt !== null)
                                <div style="font-size: 0.875rem; color: #64748b; text-decoration: line-through;">Rp {{ number_format($homeCompareAt, 0, ',', '.') }}</div>
                            @endif
                            <span style="font-size: 1.5rem; font-weight: 800; color: #818cf8;">
                                @if($homeHasPkg)<span style="font-size: 0.75rem; font-weight: 400; color: #94a3b8;">Mulai </span>@endif
                                Rp {{ number_format($homeStartingPrice, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    <a href="{{ route('product.show', $product->slug) }}" class="vt-card-cta">
                        Detail
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
