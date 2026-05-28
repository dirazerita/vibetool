<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - VibeTool.Id</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0b1120 !important; color: #e2e8f0 !important; }
        .dk-sidebar-link { display:flex; align-items:center; padding:10px 16px; font-size:14px; font-weight:500; border-radius:10px; color:#94a3b8; transition:all 0.2s; text-decoration:none; }
        .dk-sidebar-link:hover { background:rgba(99,102,241,0.1); color:#c7d2fe; }
        .dk-sidebar-link.active { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 4px 15px rgba(99,102,241,0.3); }
        .dk-sidebar-link svg { width:20px; height:20px; margin-right:12px; flex-shrink:0; }
        .dk-card { background:#1a2332; border:1px solid #2d3a4a; border-radius:12px; }
        .dk-table { background:#1a2332; border:1px solid #2d3a4a; border-radius:12px; overflow:hidden; }
        .dk-table thead { background:#151e2d; }
        .dk-table th { color:#94a3b8 !important; border-bottom:1px solid #2d3a4a; padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; }
        .dk-table td { color:#cbd5e1 !important; border-bottom:1px solid #1e2b3d; padding:16px 24px; font-size:14px; }
        .dk-table tbody tr:hover { background:#1e2b3d; }
        .dk-table tbody tr:last-child td { border-bottom:none; }
        .dk-input { background:#151e2d !important; color:#e2e8f0 !important; border:1px solid #2d3a4a !important; border-radius:8px; padding:8px 12px; font-size:14px; transition:border-color 0.2s; }
        .dk-input:focus { border-color:#6366f1 !important; outline:none; box-shadow:0 0 0 3px rgba(99,102,241,0.15); }
        .dk-input::placeholder { color:#64748b !important; }
        .dk-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 20px; border-radius:10px; font-weight:600; font-size:14px; border:none; cursor:pointer; transition:all 0.2s; text-decoration:none; }
        .dk-btn-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 2px 8px rgba(99,102,241,0.25); }
        .dk-btn-primary:hover { box-shadow:0 4px 16px rgba(99,102,241,0.4); transform:translateY(-1px); color:#fff; }
        .dk-btn-success { background:linear-gradient(135deg,#059669,#10b981); color:#fff; box-shadow:0 2px 8px rgba(16,185,129,0.25); }
        .dk-btn-success:hover { box-shadow:0 4px 16px rgba(16,185,129,0.4); transform:translateY(-1px); color:#fff; }
        .dk-btn-danger { background:linear-gradient(135deg,#dc2626,#e11d48); color:#fff; box-shadow:0 2px 8px rgba(239,68,68,0.25); }
        .dk-btn-danger:hover { box-shadow:0 4px 16px rgba(239,68,68,0.4); transform:translateY(-1px); color:#fff; }
        .dk-btn-warning { background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; box-shadow:0 2px 8px rgba(245,158,11,0.25); }
        .dk-btn-outline { background:transparent; color:#94a3b8; border:1px solid #2d3a4a; }
        .dk-btn-outline:hover { background:#1e2b3d; color:#e2e8f0; }
        .dk-badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:9999px; font-size:11px; font-weight:600; }
        .dk-alert-success { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:12px 16px; border-radius:10px; margin-bottom:24px; font-size:14px; }
        .dk-alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; padding:12px 16px; border-radius:10px; margin-bottom:24px; font-size:14px; }
        .dk-label { color:#94a3b8; font-size:14px; font-weight:500; margin-bottom:6px; display:block; }
        .dk-heading { color:#f1f5f9; }
        .dk-text-muted { color:#64748b; }
        .dk-text { color:#cbd5e1; }
        .dk-divider { border-top:1px solid #1e2b3d; margin:12px 0; }
        .dk-stat-card { background:#1a2332; border:1px solid #2d3a4a; border-radius:14px; padding:24px; display:flex; align-items:center; gap:16px; }
        .dk-stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        select.dk-input { appearance:auto; }
        textarea.dk-input { resize:vertical; }
        .dk-checkbox { appearance:none; -webkit-appearance:none; width:18px; height:18px; background:#151e2d; border:2px solid #475569; border-radius:4px; cursor:pointer; position:relative; transition:all 0.15s; flex-shrink:0; margin-top:2px; }
        .dk-checkbox:hover { border-color:#6366f1; }
        .dk-checkbox:checked { background:linear-gradient(135deg,#4f46e5,#7c3aed); border-color:#4f46e5; }
        .dk-checkbox:checked::after { content:''; position:absolute; left:4px; top:0; width:6px; height:11px; border:solid #fff; border-width:0 2px 2px 0; transform:rotate(45deg); }
        .dk-checkbox:focus-visible { outline:none; box-shadow:0 0 0 3px rgba(99,102,241,0.25); }
    </style>
</head>
<body class="font-sans antialiased" style="background:#0b1120; color:#e2e8f0;">
    <div style="min-height:100vh; display:flex;">
        <aside style="width:260px; background:linear-gradient(180deg,#0f1729 0%,#131d30 100%); flex-shrink:0; border-right:1px solid #1e2b3d; display:flex; flex-direction:column;">
            <div style="padding:24px 20px;">
                <a href="{{ route('admin.index') }}" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
                    <img src="{{ asset('logo.png') }}" alt="VibeTool.id" style="height:56px; width:auto; max-width:160px; object-fit:contain;">
                    <span style="font-size:11px; background:linear-gradient(135deg,#ef4444,#f97316); color:#fff; padding:2px 10px; border-radius:9999px; font-weight:600;">Admin</span>
                </a>
            </div>
            <nav style="padding:0 12px; flex:1; display:flex; flex-direction:column; gap:4px;">
                <a href="{{ route('admin.index') }}" class="dk-sidebar-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Overview
                </a>
                <a href="{{ route('admin.products.index') }}" class="dk-sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Produk
                </a>
                <a href="{{ route('admin.orders') }}" class="dk-sidebar-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Pesanan
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="dk-sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Kupon
                </a>
                <a href="{{ route('admin.members') }}" class="dk-sidebar-link {{ request()->routeIs('admin.members*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Member
                </a>
                <a href="{{ route('admin.commissions') }}" class="dk-sidebar-link {{ request()->routeIs('admin.commissions*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Komisi Member
                </a>
                <a href="{{ route('admin.member-commissions.index') }}" class="dk-sidebar-link {{ request()->routeIs('admin.member-commissions*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Setting Komisi Khusus
                </a>
                <a href="{{ route('admin.licenses') }}" class="dk-sidebar-link {{ request()->routeIs('admin.licenses*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Lisensi
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="dk-sidebar-link {{ request()->routeIs('admin.withdrawals') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Penarikan
                </a>
                <a href="{{ route('admin.settings') }}" class="dk-sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Pengaturan
                </a>
                <a href="{{ route('admin.profile.edit') }}" class="dk-sidebar-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profil Saya
                </a>
                <div class="dk-divider"></div>
                <a href="{{ route('dashboard') }}" class="dk-sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                    Dashboard Member
                </a>
            </nav>
        </aside>

        <div style="flex:1; overflow:auto;">
            <div style="padding:32px;">
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
