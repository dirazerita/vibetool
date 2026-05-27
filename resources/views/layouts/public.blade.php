<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VibeTool.Id - Marketplace Produk Digital')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #0b1120; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body style="background-color: #0b1120; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased;">
    <nav style="background-color: #1a2332; border-bottom: 1px solid #2d3a4a;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
            <div style="display: flex; justify-content: space-between; height: 80px; align-items: center;">
                <div style="display: flex; align-items: center;">
                    <a href="{{ route('home') }}" style="display: block; padding: 8px 0;">
                        <img src="{{ asset('logo.png') }}" alt="VibeTool.id" style="height: 64px; width: auto; max-width: 220px; object-fit: contain;">
                    </a>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                    @auth
                        <a href="{{ route('dashboard') }}" style="color: #cbd5e1; font-weight: 500; text-decoration: none;">Dashboard</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.index') }}" style="color: #cbd5e1; font-weight: 500; text-decoration: none;">Admin</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" style="color: #cbd5e1; font-weight: 500; text-decoration: none;">Login</a>
                        <a href="{{ route('register') }}" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #ffffff; padding: 8px 16px; border-radius: 8px; font-weight: 500; text-decoration: none;">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if(session('error'))
            <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; margin-top: 16px;">
                <div style="background-color: #3b1a1a; border: 1px solid #7f1d1d; color: #fca5a5; padding: 12px 16px; border-radius: 8px;">{{ session('error') }}</div>
            </div>
        @endif
        @if(session('success'))
            <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem; margin-top: 16px;">
                <div style="background-color: #1a3b2a; border: 1px solid #166534; color: #86efac; padding: 12px 16px; border-radius: 8px;">{{ session('success') }}</div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer style="background-color: #1a2332; border-top: 1px solid #2d3a4a; margin-top: 64px;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 32px 1rem; text-align: center; color: #64748b;">
            &copy; {{ date('Y') }} VibeTool.Id. Marketplace Produk Digital.
        </div>
    </footer>
</body>
</html>
