<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - VibeTool.Id</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.dk-theme')
</head>
<body class="font-sans antialiased" style="color:#e2e8f0;" x-data="{ sidebarOpen: false }">
    <div class="dk-shell">
        <div class="dk-overlay" :class="sidebarOpen ? 'dk-open' : ''" @click="sidebarOpen = false"></div>
        <aside class="dk-sidebar" :class="sidebarOpen ? 'dk-open' : ''">
            <div style="padding:24px 20px; text-align:center;">
                <a href="{{ route('home') }}" style="display:inline-block; text-decoration:none;">
                    <img src="{{ asset('logo.png') }}" alt="VibeTool.id" style="height:90px; width:auto; max-width:200px; object-fit:contain;">
                </a>
            </div>
            <div style="padding:0 20px 16px; text-align:center;">
                <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="{{ auth()->user()->name }}"
                             style="width:56px; height:56px; border-radius:50%; object-fit:cover; border:2px solid #2d3a4a;">
                    @else
                        <div style="width:56px; height:56px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:700; color:#fff; border:2px solid #2d3a4a;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div style="font-size:14px; font-weight:600; color:#e2e8f0;">{{ auth()->user()->name }}</div>
                        <div style="font-size:12px; color:#64748b;">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div style="border-bottom:1px solid #1e2b3d; margin-top:16px;"></div>
            </div>
            <nav style="padding:0 12px; flex:1; display:flex; flex-direction:column; gap:4px; overflow-y:auto;">
                <a href="{{ route('dashboard') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard') && !request()->is('dashboard/*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Overview
                </a>
                <a href="{{ route('dashboard.products') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.products') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Produk
                </a>
                <a href="{{ route('dashboard.purchases') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.purchases') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Pembelian Saya
                </a>
                <a href="{{ route('dashboard.licenses') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.licenses') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Lisensi Saya
                </a>
                <a href="{{ route('dashboard.commissions') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.commissions') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Komisi
                </a>
                <a href="{{ route('dashboard.coupons') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.coupons') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Kuponku
                </a>
                <a href="{{ route('dashboard.promo.index') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.promo.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Promo &amp; Share
                </a>
                <a href="{{ route('dashboard.team') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.team') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Tim / Downline
                </a>
                <a href="{{ route('dashboard.team-purchases') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.team-purchases') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Pembelian Tim
                </a>
                <a href="{{ route('dashboard.video-tutorials') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.video-tutorials*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    Video Tutorial
                </a>
                @if(auth()->user()->canUploadProduct())
                <a href="{{ route('dashboard.member-products') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.member-products*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Produk Saya
                </a>
                <a href="{{ route('dashboard.page-builder.index') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.page-builder.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    Page Builder
                </a>
                <a href="{{ route('dashboard.promo-templates.index') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.promo-templates*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Template Promo Saya
                </a>
                <a href="{{ route('dashboard.member-products') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.products*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                    Landing Page
                </a>
                @endif
                <a href="{{ route('dashboard.withdrawals') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.withdrawals') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Penarikan
                </a>
                <a href="{{ route('dashboard.messages') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.messages*') ? 'active' : '' }}" style="position:relative;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    Pesan
                    @if(($memberUnreadMessages ?? 0) > 0)
                        <span style="margin-left:auto; background:#ef4444; color:#fff; font-size:11px; font-weight:700; padding:1px 8px; border-radius:9999px; min-width:20px; text-align:center;">{{ $memberUnreadMessages }}</span>
                    @endif
                </a>
                <a href="{{ route('dashboard.software-requests.index') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.software-requests*') ? 'active' : '' }}" style="position:relative;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    Request Software
                    @if(($memberUnseenSoftwareResponses ?? 0) > 0)
                        <span style="margin-left:auto; background:#3b82f6; color:#fff; font-size:11px; font-weight:700; padding:1px 8px; border-radius:9999px; min-width:20px; text-align:center;">{{ $memberUnseenSoftwareResponses }}</span>
                    @endif
                </a>
                <a href="{{ route('dashboard.email-verification') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.email-verification*') ? 'active' : '' }}" style="position:relative;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Verifikasi Email
                    @if(!auth()->user()->email_verified_at)
                        <span style="margin-left:auto; background:#eab308; color:#0b1120; font-size:11px; font-weight:700; padding:1px 8px; border-radius:9999px;">!</span>
                    @endif
                </a>
                <a href="{{ route('dashboard.settings') }}" class="dk-sidebar-link {{ request()->routeIs('dashboard.settings') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Pengaturan
                </a>
                @if(auth()->user()->isAdmin())
                <div class="dk-divider"></div>
                <a href="{{ route('admin.index') }}" class="dk-sidebar-link" style="color:#f87171;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Admin Panel
                </a>
                @endif
            </nav>
            <div style="padding:12px 16px; border-top:1px solid #1e2b3d;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dk-sidebar-link" style="width:100%; color:#f87171;">
                        <svg style="width:20px; height:20px; margin-right:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="dk-main">
            <div class="dk-topbar">
                <button type="button" @click="sidebarOpen = true" style="background:none; border:none; cursor:pointer; color:#e2e8f0; padding:4px; display:flex; align-items:center;">
                    <svg style="width:26px; height:26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <a href="{{ route('home') }}" style="display:inline-block;">
                    <img src="{{ asset('logo.png') }}" alt="VibeTool.id" style="height:36px; width:auto; max-width:150px; object-fit:contain;">
                </a>
            </div>
            <div class="dk-content">
                @if(session('error'))
                    <div class="dk-alert-error">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="dk-alert-success">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
