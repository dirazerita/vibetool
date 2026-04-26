<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $landingPage->hero_title }} - PRODIG</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>img { max-width: 100%; height: auto; }</style>
</head>
<body class="font-sans antialiased bg-white">

    {{-- Navigation --}}
    <nav class="bg-white/90 backdrop-blur-sm border-b border-gray-100 fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-600">PRODIG</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-16">
        @if($landingPage->hero_image)
            <div class="relative" style="height: 500px;">
                <img src="{{ asset('storage/' . $landingPage->hero_image) }}" alt="{{ $landingPage->hero_title }}" class="w-full object-cover" style="width: 100%; height: 500px; object-fit: cover;">
                <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center px-4 max-w-4xl">
                        <h1 class="font-extrabold mb-4 leading-tight" style="font-family: '{{ $landingPage->hero_title_font ?? 'Poppins' }}', sans-serif; font-size: {{ $landingPage->hero_title_size ?? '48px' }}; color: {{ $landingPage->hero_title_color ?? '#ffffff' }};">{{ $landingPage->hero_title }}</h1>
                        @if($landingPage->hero_subtitle)
                            <p class="text-lg sm:text-xl mb-8 max-w-2xl mx-auto" style="font-family: '{{ $landingPage->hero_subtitle_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->hero_subtitle_color ?? '#e2e8f0' }};">{{ $landingPage->hero_subtitle }}</p>
                        @endif
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            @auth
                                <a href="{{ route('checkout', $product->slug) }}" class="bg-indigo-600 text-white px-8 py-4 rounded-xl hover:bg-indigo-700 font-bold text-lg shadow-lg shadow-indigo-500/30 transition-all hover:scale-105">
                                    Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-xl hover:bg-indigo-700 font-bold text-lg shadow-lg shadow-indigo-500/30 transition-all hover:scale-105">
                                    Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 pt-20 pb-20 sm:pt-28 sm:pb-28">
                <div class="max-w-4xl mx-auto text-center px-4">
                    <h1 class="font-extrabold mb-4 leading-tight" style="font-family: '{{ $landingPage->hero_title_font ?? 'Poppins' }}', sans-serif; font-size: {{ $landingPage->hero_title_size ?? '48px' }}; color: {{ $landingPage->hero_title_color ?? '#ffffff' }};">{{ $landingPage->hero_title }}</h1>
                    @if($landingPage->hero_subtitle)
                        <p class="text-lg sm:text-xl mb-8 max-w-2xl mx-auto" style="font-family: '{{ $landingPage->hero_subtitle_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->hero_subtitle_color ?? '#e2e8f0' }};">{{ $landingPage->hero_subtitle }}</p>
                    @endif
                    @auth
                        <a href="{{ route('checkout', $product->slug) }}" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl hover:bg-gray-50 font-bold text-lg shadow-lg transition-all hover:scale-105">
                            Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl hover:bg-gray-50 font-bold text-lg shadow-lg transition-all hover:scale-105">
                            Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                        </a>
                    @endauth
                </div>
            </div>
        @endif
    </section>

    {{-- Video Section --}}
    @if($landingPage->video_url)
    <section class="py-16 sm:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Lihat Video</h2>
            <div class="aspect-video rounded-2xl overflow-hidden shadow-xl">
                <iframe src="{{ \App\Helpers\VideoHelper::getEmbedUrl($landingPage->video_url) }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </section>
    @endif

    {{-- About Section --}}
    @if($landingPage->about_content)
    <section class="py-16 sm:py-20" style="background-color: {{ $landingPage->about_bg_color ?? '#ffffff' }};">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-8" style="font-family: '{{ $landingPage->about_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->about_color ?? '#374151' }};">Tentang Produk</h2>
            <div class="prose prose-lg max-w-none leading-relaxed" style="font-family: '{{ $landingPage->about_font ?? 'Poppins' }}', sans-serif; color: {{ $landingPage->about_color ?? '#374151' }};">
                {!! $landingPage->about_content !!}
            </div>
        </div>
    </section>
    @endif

    {{-- Product Info --}}
    <section class="py-16 sm:py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 text-center">
                    <div class="text-sm text-gray-500 mb-1">Harga</div>
                    <div class="text-3xl font-bold text-indigo-600">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Gallery Section --}}
    @if($product->landingPageImages->count() > 0)
    <section class="py-16 sm:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-8">Galeri</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($product->landingPageImages as $image)
                    <div class="group rounded-xl overflow-hidden shadow-sm border border-gray-200 hover:shadow-lg transition-shadow">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->caption ?? $product->title }}" class="w-full object-cover group-hover:scale-105 transition-transform duration-300" style="height: 250px; object-fit: cover;">
                        @if($image->caption)
                            <div class="p-3">
                                <p class="text-sm text-gray-600">{{ $image->caption }}</p>
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
    <section class="py-16 sm:py-20" style="background-color: {{ $landingPage->testimonial_bg_color ?? '#f9fafb' }};">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-4" style="color: {{ $landingPage->testimonial_title_color ?? '#111827' }};">Apa Kata Mereka</h2>
            <p class="text-gray-600 text-center mb-10 max-w-2xl mx-auto">Testimonial dari pengguna yang sudah merasakan manfaat produk ini.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($product->landingPageTestimonials as $testimonial)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                        <div class="flex justify-center mb-3">
                            @if($testimonial->avatar)
                                <img src="{{ asset('storage/' . $testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="rounded-full object-cover mx-auto" style="width: 64px; height: 64px;">
                            @else
                                <div class="rounded-full bg-indigo-100 flex items-center justify-center mx-auto" style="width: 64px; height: 64px;">
                                    <span class="text-indigo-600 font-bold text-xl">{{ strtoupper(substr($testimonial->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <p class="font-semibold text-gray-900 text-sm mb-1">{{ $testimonial->name }}</p>
                        <div class="flex items-center justify-center gap-0.5 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endfor
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $testimonial->content }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA Section --}}
    <section class="py-16 sm:py-20 bg-gradient-to-br from-indigo-600 to-purple-700">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Siap untuk Memulai?</h2>
            <p class="text-indigo-100 text-lg mb-8 max-w-2xl mx-auto">Dapatkan {{ $product->title }} sekarang dan mulai perjalanan Anda.</p>
            @auth
                <a href="{{ route('checkout', $product->slug) }}" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl hover:bg-gray-50 font-bold text-lg shadow-lg transition-all hover:scale-105">
                    Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl hover:bg-gray-50 font-bold text-lg shadow-lg transition-all hover:scale-105">
                    Beli Sekarang — Rp {{ number_format($product->price, 0, ',', '.') }}
                </a>
            @endauth
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-gray-500">
            &copy; {{ date('Y') }} PRODIG. Marketplace Produk Digital.
        </div>
    </footer>

    {{-- Sticky CTA Bar --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-gray-200 shadow-lg z-50 py-3 px-4 sm:px-6" id="sticky-cta">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="hidden sm:block">
                <p class="font-semibold text-gray-900 text-sm">{{ $product->title }}</p>
                <p class="text-indigo-600 font-bold text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            </div>
            <div class="sm:hidden">
                <p class="text-indigo-600 font-bold text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            </div>
            @auth
                <a href="{{ route('checkout', $product->slug) }}" class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 font-bold text-sm sm:text-base shadow-md transition-all hover:scale-105 whitespace-nowrap">
                    Beli Sekarang
                </a>
            @else
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 font-bold text-sm sm:text-base shadow-md transition-all hover:scale-105 whitespace-nowrap">
                    Beli Sekarang
                </a>
            @endauth
        </div>
    </div>

    {{-- Bottom padding for sticky bar --}}
    <div class="h-20"></div>
</body>
</html>
