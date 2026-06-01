<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $landingPage->hero_title }} - VibeTool.Id</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        html, body { max-width: 100%; overflow-x: hidden; }
        img { max-width: 100%; height: auto; }
        body { background-color: #0b1120; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased; }

        /* Responsive grids */
        .vt-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        @media (max-width: 1024px) {
            .vt-grid-3 { grid-template-columns: repeat(2, 1fr) !important; }
        }

        @media (max-width: 640px) {
            .vt-grid-3 { grid-template-columns: 1fr !important; }
            .vt-nav-logo { height: 44px !important; max-width: 150px !important; }
            .vt-nav-row { height: 60px !important; }
            .vt-nav-actions { gap: 10px !important; }
            .vt-nav-actions a { font-size: 0.875rem !important; }
            .vt-hero-media { height: 360px !important; }
            .vt-hero-media img { height: 360px !important; }
            .vt-hero-title { font-size: 1.875rem !important; line-height: 1.2 !important; }
            .vt-hero-subtitle { font-size: 1rem !important; }
            .vt-section { padding: 48px 0 !important; }
            .vt-section-title { font-size: 1.5rem !important; }
            .vt-cta-title { font-size: 1.75rem !important; }
            .vt-cta-btn { width: 100% !important; text-align: center !important; }
        }

        .vt-prose { font-size: 1rem; line-height: 1.75; }
        .vt-prose > * + * { margin-top: 1rem; }
        .vt-prose h1 { font-size: 2rem; font-weight: 700; line-height: 1.2; margin: 1.5em 0 0.5em; }
        .vt-prose h2 { font-size: 1.5rem; font-weight: 700; line-height: 1.25; margin: 1.5em 0 0.5em; }
        .vt-prose h3 { font-size: 1.25rem; font-weight: 600; line-height: 1.3; margin: 1.25em 0 0.5em; }
        .vt-prose h4 { font-size: 1.125rem; font-weight: 600; line-height: 1.3; margin: 1.25em 0 0.5em; }
        .vt-prose h5, .vt-prose h6 { font-size: 1rem; font-weight: 600; margin: 1em 0 0.5em; }
        .vt-prose p { margin: 0.75em 0; }
        .vt-prose ul, .vt-prose ol { margin: 0.75em 0; padding-left: 1.5rem; }
        .vt-prose ul { list-style: disc; }
        .vt-prose ol { list-style: decimal; }
        .vt-prose li { margin: 0.25em 0; }
        .vt-prose li > ul, .vt-prose li > ol { margin: 0.25em 0; }
        .vt-prose a { color: #4f46e5; text-decoration: underline; }
        .vt-prose a:hover { color: #4338ca; }
        .vt-prose blockquote { border-left: 4px solid #c7d2fe; padding: 0.5rem 1rem; margin: 1em 0; color: #4b5563; background: #f5f3ff; border-radius: 0 8px 8px 0; }
        .vt-prose hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
        .vt-prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
        .vt-prose code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875em; background: #f3f4f6; padding: 0.125em 0.375em; border-radius: 4px; }
        .vt-prose pre { background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 8px; overflow-x: auto; margin: 1em 0; }
        .vt-prose pre code { background: transparent; padding: 0; color: inherit; }
        .vt-prose table { border-collapse: collapse; width: 100%; margin: 1em 0; }
        .vt-prose th, .vt-prose td { border: 1px solid #e5e7eb; padding: 0.5rem 0.75rem; text-align: left; }
        .vt-prose th { background: #f9fafb; font-weight: 600; }
        .vt-prose strong { font-weight: 700; }
        .vt-prose em { font-style: italic; }
    </style>
</head>
<body style="background-color: #0b1120; color: #e2e8f0; font-family: Figtree, sans-serif; -webkit-font-smoothing: antialiased;">
    @php
        $registerUrl = route('register');
        if (session('ref_code')) {
            $registerUrl .= '?ref=' . urlencode(session('ref_code'));
        }
        $isFree = $product->isFree();
        $packages = $product->activePackages;
        $hasPackages = $packages->isNotEmpty();
        $defaultPackage = $hasPackages ? $packages->first() : null;
        $displayPrice = $hasPackages ? (float) $defaultPackage->price : (float) $product->price;
        $displayCompareAt = $hasPackages
            ? ($defaultPackage->compare_at_price !== null ? (float) $defaultPackage->compare_at_price : null)
            : ($product->compare_at_price !== null ? (float) $product->compare_at_price : null);
        $ctaLabel = $isFree
            ? 'Dapatkan Gratis'
            : ('Beli Sekarang — Rp ' . number_format($displayPrice, 0, ',', '.'));
        $ctaCheckoutUrl = null;
        if (! $isFree) {
            $ctaCheckoutUrl = $hasPackages
                ? route('checkout', $product->slug) . '?package_id=' . $defaultPackage->id
                : route('checkout', $product->slug);
        }
        $freeClaimUrl = $isFree ? route('free.claim', $product->slug) : null;
    @endphp

    {{-- Navigation --}}
    <nav style="background-color: #1a2332; border-bottom: 1px solid #2d3a4a; position: relative; z-index: 30;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
            <div class="vt-nav-row" style="display: flex; justify-content: space-between; height: 80px; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <a href="{{ route('home') }}" style="display: block; padding: 8px 0;">
                        <img src="{{ asset('logo.png') }}" alt="VibeTool.id" class="vt-nav-logo" style="height: 64px; width: auto; max-width: 220px; object-fit: contain;">
                    </a>
                </div>
                <div class="vt-nav-actions" style="display: flex; align-items: center; gap: 16px;">
                    @auth
                        <a href="{{ route('dashboard') }}" style="color: #cbd5e1; font-weight: 500; text-decoration: none;">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" style="color: #cbd5e1; font-weight: 500; text-decoration: none;">Login</a>
                        <a href="{{ $registerUrl }}" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 8px 16px; border-radius: 8px; font-weight: 500; text-decoration: none;">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section style="position: relative;">
        @if($landingPage->hero_image)
            <div class="vt-hero-media" style="position: relative; height: 500px;">
                <img src="{{ asset('storage/' . $landingPage->hero_image) }}" alt="{{ $landingPage->hero_title }}" fetchpriority="high" decoding="async" style="width: 100%; height: 500px; object-fit: cover;">
                <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.4), rgba(0,0,0,0.7));"></div>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                    <div style="text-align: center; padding: 0 16px; max-width: 56rem;">
                        <h1 class="vt-hero-title" style="font-family: '{{ $landingPage->hero_title_font ?? 'Poppins' }}', sans-serif; font-size: {{ $landingPage->hero_title_size ?? '48px' }}; color: {{ $landingPage->hero_title_color ?? '#ffffff' }};">{{ $landingPage->hero_title }}</h1>
                        @if($landingPage->hero_subtitle)
                            <p class="vt-hero-subtitle" style="font-family: '{{ $landingPage->hero_subtitle_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->hero_subtitle_color ?? '#e2e8f0' }};">{{ $landingPage->hero_subtitle }}</p>
                        @endif
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;">
                            @auth
                                @if($isFree)
                                    <form method="POST" action="{{ $freeClaimUrl }}">@csrf
                                        <button type="submit" style="background: #10b981; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: 0; cursor: pointer;">{{ $ctaLabel }}</button>
                                    </form>
                                @else
                                    <a href="{{ $ctaCheckoutUrl }}" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
                                @endif
                            @else
                                <a href="{{ $registerUrl }}" style="background: {{ $isFree ? '#10b981' : 'linear-gradient(135deg, #4f46e5, #7c3aed)' }}; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div style="background: linear-gradient(135deg, #1e1b4b, #312e81, #4c1d95); padding: 80px 0;">
                <div style="max-width: 56rem; margin: 0 auto; text-align: center; padding: 0 16px;">
                    <h1 class="vt-hero-title" style="font-family: '{{ $landingPage->hero_title_font ?? 'Poppins' }}', sans-serif; font-size: {{ $landingPage->hero_title_size ?? '48px' }}; color: {{ $landingPage->hero_title_color ?? '#ffffff' }};">{{ $landingPage->hero_title }}</h1>
                    @if($landingPage->hero_subtitle)
                        <p class="vt-hero-subtitle" style="font-family: '{{ $landingPage->hero_subtitle_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->hero_subtitle_color ?? '#e2e8f0' }};">{{ $landingPage->hero_subtitle }}</p>
                    @endif
                    @auth
                        @if($isFree)
                            <form method="POST" action="{{ $freeClaimUrl }}" style="display: inline-block;">@csrf
                                <button type="submit" style="background: #10b981; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: 0; cursor: pointer;">{{ $ctaLabel }}</button>
                            </form>
                        @else
                            <a href="{{ $ctaCheckoutUrl }}" style="display: inline-block; background: linear-gradient(135deg, #6366f1, #a78bfa); color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
                        @endif
                    @else
                        <a href="{{ $registerUrl }}" style="display: inline-block; background: {{ $isFree ? '#10b981' : 'linear-gradient(135deg, #6366f1, #a78bfa)' }}; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
                    @endauth
                </div>
            </div>
        @endif
    </section>

    {{-- Video Section --}}
    @if($landingPage->video_url)
    <section style="padding: 80px 0; background-color: #151e2d;">
        <div style="max-width: 56rem; margin: 0 auto; padding: 0 1rem;">
            <h2 style="font-size: 1.875rem; font-weight: 700; color: #e2e8f0; text-align: center; margin-bottom: 32px;">Lihat Video</h2>
            <div style="aspect-ratio: 16/9; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                <iframe src="{{ \App\Helpers\VideoHelper::getEmbedUrl($landingPage->video_url) }}" style="width: 100%; height: 100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </section>
    @endif

    {{-- About Section --}}
    @if($landingPage->about_content)
    <section style="padding: 80px 0; background-color: {{ $landingPage->about_bg_color ?? '#ffffff' }};">
        <div style="max-width: 56rem; margin: 0 auto; padding: 0 1rem;">
            <h2 style="font-family: '{{ $landingPage->about_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->about_color ?? '#374151' }}; font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">Tentang Produk</h2>
            <div class="vt-prose" style="font-family: '{{ $landingPage->about_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->about_color ?? '#374151' }};">
                {!! $landingPage->about_content !!}
            </div>
        </div>
    </section>
    @endif

    {{-- Product Info / Package Picker --}}
    @unless($isFree)
    <section style="padding: 80px 0; background-color: #151e2d;">
        <div style="max-width: 64rem; margin: 0 auto; padding: 0 1rem;">
            @if($hasPackages)
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #e2e8f0; text-align: center; margin-bottom: 24px;">Pilih Paket</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    @foreach($packages as $pkg)
                        @php
                            $pkgDurationLabel = match($pkg->duration_type) {
                                '1_month' => '1 Bulan',
                                '6_months' => '6 Bulan',
                                '1_year' => '1 Tahun',
                                'lifetime' => 'Lifetime',
                                default => $pkg->duration_type,
                            };
                            $pkgHasCompare = $pkg->compare_at_price !== null && (float) $pkg->compare_at_price > (float) $pkg->price;
                        @endphp
                        <a href="{{ route('checkout', $product->slug) }}?package_id={{ $pkg->id }}" style="display: block; background-color: #1a2332; border-radius: 12px; padding: 24px; border: 1px solid {{ $pkg->id === $defaultPackage->id ? '#6366f1' : '#2d3a4a' }}; text-decoration: none; transition: transform 0.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 4px;">{{ $pkg->displayLabel() }}</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;">Durasi: {{ $pkgDurationLabel }}</div>
                            @if($pkgHasCompare)
                                <div style="font-size: 0.875rem; color: #64748b; text-decoration: line-through; margin-bottom: 2px;">Rp {{ number_format($pkg->compare_at_price, 0, ',', '.') }}</div>
                            @endif
                            <div style="font-size: 1.5rem; font-weight: 700; color: #818cf8;">Rp {{ number_format($pkg->price, 0, ',', '.') }}</div>
                            <div style="margin-top: 16px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 10px 16px; border-radius: 8px; font-weight: 600; text-align: center;">Pilih Paket</div>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="display: flex; justify-content: center;">
                    <div style="background-color: #1a2332; border-radius: 12px; padding: 24px; border: 1px solid #2d3a4a; text-align: center;">
                        <div style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 4px;">Harga</div>
                        @if($displayCompareAt !== null && $displayCompareAt > $displayPrice)
                            <div style="font-size: 1rem; color: #64748b; text-decoration: line-through;">Rp {{ number_format($displayCompareAt, 0, ',', '.') }}</div>
                        @endif
                        <div style="font-size: 1.875rem; font-weight: 700; color: #818cf8;">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    @endunless

    {{-- Gallery Section --}}
    @if($product->landingPageImages->count() > 0)
    <section class="py-16 sm:py-20">
        <div style="max-width: 72rem; margin: 0 auto; padding: 0 1rem;">
            <h2 style="font-size: 1.875rem; font-weight: 700; color: #e2e8f0; text-align: center; margin-bottom: 32px;">Galeri</h2>
            <div class="vt-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                @foreach($product->landingPageImages as $image)
                    <div style="border-radius: 12px; overflow: hidden; border: 1px solid #2d3a4a; background-color: #1a2332;">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->caption ?? $product->title }}" loading="lazy" decoding="async" style="width: 100%; height: 250px; object-fit: cover;">
                        @if($image->caption)
                            <div style="padding: 12px;">
                                <p style="font-size: 0.875rem; color: #94a3b8;">{{ $image->caption }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Testimonial Section --}}
    @if($product->landingPageTestimonials->count() > 0)
    <section style="padding: 80px 0; background-color: {{ $landingPage->testimonial_bg_color ?? '#f9fafb' }};">
        <div style="max-width: 72rem; margin: 0 auto; padding: 0 1rem;">
            <h2  style="color: {{ $landingPage->testimonial_title_color ?? '#111827' }};">Apa Kata Mereka</h2>
            <p style="color: #94a3b8; text-align: center; margin-bottom: 40px; max-width: 42rem; margin-left: auto; margin-right: auto;">Testimonial dari pengguna yang sudah merasakan manfaat produk ini.</p>
            <div class="vt-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                @foreach($product->landingPageTestimonials as $testimonial)
                    <div style="background-color: #1a2332; border-radius: 12px; border: 1px solid #2d3a4a; padding: 24px; text-align: center;">
                        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                            @if($testimonial->avatar)
                                <img src="{{ asset('storage/' . $testimonial->avatar) }}" alt="{{ $testimonial->name }}" loading="lazy" decoding="async" style="border-radius: 50%; object-fit: cover; width: 64px; height: 64px;">
                            @else
                                <div style="border-radius: 50%; background-color: #312e81; display: flex; align-items: center; justify-content: center; width: 64px; height: 64px;">
                                    <span style="color: #818cf8; font-weight: 700; font-size: 1.25rem;">{{ strtoupper(substr($testimonial->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <p style="font-weight: 600; color: #e2e8f0; font-size: 0.875rem; margin-bottom: 4px;">{{ $testimonial->name }}</p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 2px; margin-bottom: 12px;">
                            @for($i = 1; $i <= 5; $i++)
                                <svg style="width: 16px; height: 16px; color: {{ $i <= $testimonial->rating ? '#facc15' : '#475569' }};" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <p style="color: #94a3b8; font-size: 0.875rem; line-height: 1.625;">{{ $testimonial->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA Section --}}
    <section style="padding: 80px 0; background: linear-gradient(135deg, #1e1b4b, #312e81, #4c1d95);">
        <div style="max-width: 56rem; margin: 0 auto; text-align: center; padding: 0 16px;">
            <h2 class="vt-cta-title" style="font-size: 2.25rem; font-weight: 700; color: #ffffff; margin-bottom: 16px;">Siap untuk Memulai?</h2>
            <p style="color: #c7d2fe; font-size: 1.125rem; margin-bottom: 32px; max-width: 42rem; margin-left: auto; margin-right: auto;">Dapatkan {{ $product->title }} sekarang dan mulai perjalanan Anda.</p>
            @auth
                @if($isFree)
                    <form method="POST" action="{{ $freeClaimUrl }}" style="display: inline-block;">@csrf
                        <button type="submit" style="background: #10b981; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(16,185,129,0.4); border: 0; cursor: pointer;">{{ $ctaLabel }}</button>
                    </form>
                @else
                    <a href="{{ $ctaCheckoutUrl }}" style="display: inline-block; background: linear-gradient(135deg, #6366f1, #a78bfa); color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
                @endif
            @else
                <a href="{{ $registerUrl }}" style="display: inline-block; background: {{ $isFree ? '#10b981' : 'linear-gradient(135deg, #6366f1, #a78bfa)' }}; color: #ffffff; padding: 16px 32px; border-radius: 12px; font-weight: 700; font-size: 1.125rem; box-shadow: 0 4px 15px rgba(99,102,241,0.4); text-decoration: none;">{{ $ctaLabel }}</a>
            @endauth
        </div>
    </section>

    {{-- Footer --}}
    <footer style="background-color: #1a2332; border-top: 1px solid #2d3a4a;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 32px 1rem; text-align: center; color: #64748b;">
            &copy; {{ date('Y') }} VibeTool.Id. Marketplace Produk Digital.
        </div>
    </footer>

    {{-- Sticky CTA bar dihilangkan: CTA sudah tersedia di hero section dan section CTA bawah. --}}
</body>
</html>
