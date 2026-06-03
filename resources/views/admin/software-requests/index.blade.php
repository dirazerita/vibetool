@extends('layouts.admin')
@section('title', 'Request Software')

@section('content')
<style>
    .sr-stat-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:24px; }
    .sr-stat { padding:14px 16px; border-radius:10px; border:1px solid #1e2b3d; background:#0f1729; cursor:pointer; transition:all 0.15s; text-decoration:none; }
    .sr-stat:hover { transform:translateY(-1px); border-color:#334155; }
    .sr-stat.active { border-color:#6366f1; background:rgba(99,102,241,0.08); }
    .sr-stat-label { font-size:12px; color:#94a3b8; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.4px; }
    .sr-stat-value { font-size:22px; font-weight:700; color:#e2e8f0; }
    .sr-stat-color { display:inline-block; width:8px; height:8px; border-radius:9999px; margin-right:6px; vertical-align:middle; }
    .sr-row-link { color:#e2e8f0; text-decoration:none; font-weight:500; }
    .sr-row-link:hover { color:#a5b4fc; }
    .sr-status-pill { display:inline-block; padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:600; color:#fff; }
    .sr-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
    .sr-search { width:100%; max-width:320px; background:#151e2d; border:1px solid #2d3a4a; color:#e2e8f0; padding:8px 12px; border-radius:8px; font-size:14px; }
</style>

<div style="margin-bottom:20px;">
    <h1 class="text-2xl font-bold dk-heading">Request Software dari Member</h1>
    <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Daftar ide aplikasi/software yang diminta member. Klik baris untuk lihat detail dan update status.</p>
</div>

<div class="sr-stat-grid">
    <a href="{{ route('admin.software-requests.index') }}" class="sr-stat {{ ! $currentStatus ? 'active' : '' }}">
        <div class="sr-stat-label">Semua</div>
        <div class="sr-stat-value">{{ array_sum($statusCounts) }}</div>
    </a>
    @foreach(\App\Models\SoftwareRequest::STATUSES as $statusKey => $statusLabel)
        <a href="{{ route('admin.software-requests.index', ['status' => $statusKey]) }}" class="sr-stat {{ $currentStatus === $statusKey ? 'active' : '' }}">
            <div class="sr-stat-label">
                <span class="sr-stat-color" style="background:{{ \App\Models\SoftwareRequest::STATUS_COLORS[$statusKey] }};"></span>
                {{ $statusLabel }}
            </div>
            <div class="sr-stat-value">{{ $statusCounts[$statusKey] ?? 0 }}</div>
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.software-requests.index') }}" class="sr-toolbar">
    @if($currentStatus)
        <input type="hidden" name="status" value="{{ $currentStatus }}">
    @endif
    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, atau judul request..." class="sr-search">
    <div style="display:flex; gap:8px;">
        <button type="submit" class="dk-btn dk-btn-outline">Cari</button>
        @if($search)
            <a href="{{ route('admin.software-requests.index', ['status' => $currentStatus]) }}" class="dk-btn dk-btn-outline">Reset</a>
        @endif
    </div>
</form>

@if(session('success'))
    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:14px;">
        {{ session('success') }}
    </div>
@endif

<div class="dk-table" style="overflow-x:auto;">
    <table style="width:100%; min-width:760px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Member</th>
                <th>Judul Aplikasi</th>
                <th>Platform</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td style="white-space:nowrap;">
                        <div style="color:#cbd5e1; font-size:13px;">{{ $req->created_at->format('d M Y') }}</div>
                        <div class="dk-text-muted" style="font-size:11px;">{{ $req->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="color:#e2e8f0;">{{ $req->user->name ?? '—' }}</div>
                        <div class="dk-text-muted" style="font-size:12px;">{{ $req->user->email ?? '' }}</div>
                    </td>
                    <td style="max-width:280px;">
                        <a href="{{ route('admin.software-requests.show', $req) }}" class="sr-row-link">{{ $req->title }}</a>
                        <div class="dk-text-muted" style="font-size:12px;">{{ \Illuminate\Support\Str::limit($req->purpose, 80) }}</div>
                    </td>
                    <td style="font-size:12px; color:#94a3b8;">
                        {{ \Illuminate\Support\Str::limit(implode(', ', $req->platformLabels()), 40) }}
                    </td>
                    <td>
                        <span class="sr-status-pill" style="background:{{ $req->statusColor() }};">{{ $req->statusLabel() }}</span>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('admin.software-requests.show', $req) }}" class="dk-btn dk-btn-outline" style="padding:5px 12px; font-size:12px;">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px 16px; color:#64748b;">
                        Belum ada request software dari member.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $requests->links() }}
</div>
@endsection
