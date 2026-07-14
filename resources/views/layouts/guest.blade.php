@props(['logoHeight' => 144, 'logoMaxWidth' => 360])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            * { box-sizing: border-box; }
            body { background-color: #070b17; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased; margin: 0; }
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                background:
                    radial-gradient(ellipse 55% 45% at 20% 0%, rgba(99, 102, 241, 0.2), transparent 60%),
                    radial-gradient(ellipse 50% 40% at 85% 20%, rgba(139, 92, 246, 0.14), transparent 60%),
                    radial-gradient(ellipse 55% 45% at 50% 110%, rgba(56, 189, 248, 0.09), transparent 60%),
                    #070b17;
            }
            .vt-auth-card {
                position: relative;
                width: 100%;
                max-width: 28rem;
                margin-top: 24px;
                padding: 28px;
                background: linear-gradient(160deg, rgba(30, 38, 66, 0.75), rgba(17, 23, 43, 0.7));
                -webkit-backdrop-filter: blur(18px);
                backdrop-filter: blur(18px);
                border: 1px solid rgba(129, 140, 248, 0.18);
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(2, 6, 23, 0.55), 0 0 40px rgba(99, 102, 241, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.06);
                animation: vtAuthIn 0.5s ease both;
            }
            .vt-auth-card::before {
                content: '';
                position: absolute;
                top: 0; left: 15%; right: 15%; height: 1px;
                background: linear-gradient(90deg, transparent, rgba(129, 140, 248, 0.6), transparent);
            }
            @keyframes vtAuthIn { from { opacity: 0; transform: translateY(16px) } to { opacity: 1; transform: translateY(0) } }
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
            }
        </style>
    </head>
    <body style="background-color: #070b17; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased;">
        <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 40px 16px; position: relative; z-index: 1;">
            <div>
                <a href="/">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="height: {{ $logoHeight }}px; width: auto; max-width: {{ $logoMaxWidth }}px; object-fit: contain;">
                </a>
            </div>

            <div class="vt-auth-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
