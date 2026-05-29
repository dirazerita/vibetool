@extends('layouts.admin')
@section('title', 'Broadcast')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Broadcast</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Kirim pesan ke banyak member sekaligus. Pesan masuk ke thread chat masing-masing member.</p>
    </div>
    <a href="{{ route('admin.broadcasts.create') }}" class="dk-btn dk-btn-primary">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Broadcast
    </a>
</div>

<div style="display:flex; gap:16px; margin-bottom:24px;">
    <div class="dk-stat-card" style="flex:1;">
        <div class="dk-stat-icon" style="background:rgba(79,70,229,0.15); color:#a5b4fc;">
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:13px;">Semua Member</div>
            <div class="dk-heading" style="font-size:24px; font-weight:700;">{{ number_format($audienceCounts['all']) }}</div>
        </div>
    </div>
    <div class="dk-stat-card" style="flex:1;">
        <div class="dk-stat-icon" style="background:rgba(16,185,129,0.15); color:#6ee7b7;">
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:13px;">Member Aktif</div>
            <div class="dk-heading" style="font-size:24px; font-weight:700;">{{ number_format($audienceCounts['active']) }}</div>
        </div>
    </div>
    <div class="dk-stat-card" style="flex:1;">
        <div class="dk-stat-icon" style="background:rgba(217,119,6,0.15); color:#fbbf24;">
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:13px;">Total Broadcast Terkirim</div>
            <div class="dk-heading" style="font-size:24px; font-weight:700;">{{ number_format($broadcasts->total()) }}</div>
        </div>
    </div>
</div>

<div class="dk-table">
    <table style="width:100%;">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Audience</th>
                <th>Pesan</th>
                <th>Lampiran</th>
                <th style="text-align:right;">Penerima</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($broadcasts as $b)
                <tr>
                    <td style="white-space:nowrap;">
                        <div style="color:#cbd5e1;">{{ $b->sent_at?->format('d M Y') ?? '—' }}</div>
                        <div class="dk-text-muted" style="font-size:12px;">{{ $b->sent_at?->format('H:i') }}</div>
                    </td>
                    <td>
                        <span class="dk-badge" style="background:rgba(99,102,241,0.15); color:#a5b4fc;">{{ $b->audienceLabel() }}</span>
                    </td>
                    <td style="max-width:360px;">
                        @if($b->body)
                            <span style="color:#cbd5e1;">{{ \Illuminate\Support\Str::limit($b->body, 100) }}</span>
                        @else
                            <span class="dk-text-muted" style="font-style:italic;">(hanya lampiran)</span>
                        @endif
                    </td>
                    <td>
                        @if($b->hasAttachment())
                            <a href="{{ route('admin.broadcasts.attachment', $b) }}" class="dk-text" style="font-size:13px; text-decoration:underline;">{{ \Illuminate\Support\Str::limit($b->attachment_name, 30) }}</a>
                        @else
                            <span class="dk-text-muted">—</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-weight:600; color:#cbd5e1;">{{ number_format($b->recipients_count) }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.broadcasts.show', $b) }}" class="dk-btn dk-btn-outline" style="padding:6px 14px;">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:32px;" class="dk-text-muted">Belum ada broadcast.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($broadcasts->hasPages())
    <div style="margin-top:16px;">{{ $broadcasts->links() }}</div>
@endif
@endsection
