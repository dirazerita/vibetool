<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VibeTool.Id - Marketplace Produk Digital')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        html, body { max-width: 100%; overflow-x: hidden; }
        html { scroll-behavior: smooth; }
        body { background-color: #070b17; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; }

        /* Aurora ambient background (radial-gradient murni — ringan di mobile) */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(ellipse 60% 45% at 15% -5%, rgba(99, 102, 241, 0.18), transparent 60%),
                radial-gradient(ellipse 50% 40% at 92% 8%, rgba(139, 92, 246, 0.13), transparent 60%),
                radial-gradient(ellipse 55% 45% at 50% 110%, rgba(56, 189, 248, 0.08), transparent 60%),
                #070b17;
        }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.25); border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.45); }

        /* Glass sticky nav */
        .vt-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(9, 13, 26, 0.72);
            -webkit-backdrop-filter: blur(16px);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(129, 140, 248, 0.12);
        }
        .vt-nav-link { color: #cbd5e1; font-weight: 500; text-decoration: none; padding: 6px 12px; border-radius: 10px; transition: color 0.2s ease, background 0.2s ease; }
        .vt-nav-link:hover { color: #fff; background: rgba(99, 102, 241, 0.12); }
        .vt-nav-cta {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff; padding: 9px 20px; border-radius: 12px; font-weight: 600; text-decoration: none;
            box-shadow: 0 2px 14px rgba(99, 102, 241, 0.35), inset 0 1px 0 rgba(255,255,255,0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .vt-nav-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(99, 102, 241, 0.5), inset 0 1px 0 rgba(255,255,255,0.18); filter: brightness(1.08); }

        /* Alert glass */
        .vt-alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 12px 16px; border-radius: 12px; -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }
        .vt-alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #6ee7b7; padding: 12px 16px; border-radius: 12px; -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }

        /* Responsive product grid (nama class & breakpoint dipertahankan) */
        .vt-product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }

        @media (max-width: 1024px) {
            .vt-product-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 24px !important; }
        }

        @media (max-width: 640px) {
            .vt-product-grid { grid-template-columns: 1fr !important; gap: 20px !important; }
            .vt-hero { padding: 40px 0 !important; }
            .vt-hero h1 { font-size: 1.85rem !important; }
            .vt-hero p { font-size: 1rem !important; }
            .vt-nav-logo { height: 44px !important; max-width: 150px !important; }
            .vt-nav-row { height: 60px !important; }
            .vt-nav-actions { gap: 10px !important; }
            .vt-nav-actions a { font-size: 0.875rem !important; }
            .vt-modal-actions { flex-direction: column !important; }
            .vt-section-pad { padding: 32px 1rem !important; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body style="color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased;">
    <nav class="vt-nav">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
            <div class="vt-nav-row" style="display: flex; justify-content: space-between; height: 80px; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <a href="{{ route('home') }}" style="display: block; padding: 8px 0;">
                        <img src="{{ asset('logo.png') }}" alt="VibeTool.id" class="vt-nav-logo" style="height: 64px; width: auto; max-width: 220px; object-fit: contain;">
                    </a>
                </div>
                <div class="vt-nav-actions" style="display: flex; align-items: center; gap: 12px;">
                    @auth
                        <a href="{{ route('dashboard') }}" class="vt-nav-link">Dashboard</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.index') }}" class="vt-nav-link">Admin</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="vt-nav-link">Login</a>
                        <a href="{{ route('register') }}" class="vt-nav-cta">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if(session('error'))
            <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; margin-top: 16px;">
                <div class="vt-alert-error">{{ session('error') }}</div>
            </div>
        @endif
        @if(session('success'))
            <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; margin-top: 16px;">
                <div class="vt-alert-success">{{ session('success') }}</div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer style="background: rgba(9, 13, 26, 0.7); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); border-top: 1px solid rgba(129, 140, 248, 0.12); margin-top: 64px;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 40px 1rem 32px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ asset('logo.png') }}" alt="VibeTool.id" style="height: 40px; width: auto; max-width: 140px; object-fit: contain;">
                    <span style="color: #94a3b8; font-size: 0.875rem;">Marketplace Produk Digital</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.8rem;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: #6ee7b7; padding: 4px 12px; border-radius: 9999px; font-weight: 600;">
                        <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Pembayaran Aman
                    </span>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(148, 163, 184, 0.1); margin-top: 24px; padding-top: 20px; text-align: center; color: #64748b; font-size: 0.85rem;">
                &copy; {{ date('Y') }} VibeTool.Id. Marketplace Produk Digital.
            </div>
        </div>
    </footer>
</body>
</html>
