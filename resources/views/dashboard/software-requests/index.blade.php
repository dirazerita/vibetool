@extends('layouts.dashboard')
@section('title', 'Request Software')

@section('content')
<style>
    .sr-empty { text-align:center; padding:48px 16px; color:#64748b; }
    .sr-empty svg { width:48px; height:48px; margin:0 auto 12px; opacity:0.5; }
    .sr-card { display:block; background:#0f1729; border:1px solid #1e2b3d; border-radius:14px; padding:20px; text-decoration:none; color:inherit; transition:all 0.15s; }
    .sr-card:hover { border-color:#334155; transform:translateY(-1px); }
    .sr-status-pill { display:inline-block; padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:600; color:#fff; }
    .sr-card-title { font-size:16px; font-weight:600; color:#e2e8f0; margin-bottom:6px; }
    .sr-card-desc { font-size:13px; color:#94a3b8; line-height:1.5; max-height:42px; overflow:hidden; }
    .sr-card-meta { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:12px; font-size:12px; color:#64748b; }
    .sr-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; }
    .sr-resp-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:9999px; background:rgba(59,130,246,0.15); color:#93c5fd; font-size:11px; font-weight:600; }
</style>

<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Request Software</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px; max-width:560px;">
            Punya ide aplikasi atau software yang kamu butuhkan? Cerita di sini, tim kami akan review dan kabari kamu.
        </p>
    </div>
    <a href="{{ route('dashboard.software-requests.create') }}" class="dk-btn dk-btn-primary">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Kirim Request Baru
    </a>
</div>

@if(session('success'))
    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px;">
        {{ session('success') }}
    </div>
@endif

@if($requests->isEmpty())
    <div class="dk-card sr-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <p style="font-size:15px; color:#94a3b8; margin-bottom:4px;">Belum ada request.</p>
        <p style="font-size:13px;">Klik "Kirim Request Baru" di atas untuk mulai cerita ide aplikasi kamu.</p>
    </div>
@else
    <div class="sr-grid">
        @foreach($requests as $req)
            <a href="{{ route('dashboard.software-requests.show', $req) }}" class="sr-card">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px;">
                    <span class="sr-status-pill" style="background:{{ $req->statusColor() }};">{{ $req->statusLabel() }}</span>
                    @if($req->hasUnseenResponse())
                        <span class="sr-resp-badge">
                            <svg style="width:10px; height:10px;" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
                            Ada respon baru
                        </span>
                    @endif
                </div>
                <div class="sr-card-title">{{ $req->title }}</div>
                <div class="sr-card-desc">{{ \Illuminate\Support\Str::limit($req->purpose, 140) }}</div>
                <div class="sr-card-meta">
                    <span>{{ $req->created_at->timezone(config('app.timezone'))->format('d M Y') }}</span>
                    @if(! empty($req->platforms))
                        <span>·</span>
                        <span>{{ \Illuminate\Support\Str::limit(implode(', ', $req->platformLabels()), 40) }}</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div style="margin-top:24px;">
        {{ $requests->links() }}
    </div>
@endif
@endsection
