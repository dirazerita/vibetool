<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { background-color: #0b1120; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased; }
        </style>
    </head>
    <body style="background-color: #0b1120; color: #e2e8f0; font-family: 'Figtree', sans-serif; -webkit-font-smoothing: antialiased;">
        <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding-top: 48px; background-color: #0b1120;">
            <div>
                <a href="/">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 64px; width: auto; max-width: 220px; object-fit: contain;">
                </a>
            </div>

            <div style="width: 100%; max-width: 28rem; margin-top: 24px; padding: 24px; background-color: #1a2332; border: 1px solid #2d3a4a; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
