@extends('layouts.admin')
@section('title', 'Detail Broadcast')

@section('content')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
    <a href="{{ route('admin.broadcasts.index') }}" class="dk-btn dk-btn-outline" style="padding:6px 12px;">
        <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <div>
        <h1 class="text-2xl font-bold dk-heading">Detail Broadcast</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Terkirim {{ $broadcast->sent_at?->format('d M Y H:i') }}</p>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px;">
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(79,70,229,0.15); color:#a5b4fc;">
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:13px;">Penerima</div>
            <div class="dk-heading" style="font-size:22px; font-weight:700;">{{ number_format($broadcast->recipients_count) }}</div>
            <div class="dk-text-muted" style="font-size:12px;">{{ $broadcast->audienceLabel() }}</div>
        </div>
    </div>
    <div class="dk-stat-card">
        <div class="dk-stat-icon" style="background:rgba(16,185,129,0.15); color:#6ee7b7;">
            <svg style="width:24px; height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
        </div>
        <div>
            <div class="dk-text-muted" style="font-size:13px;">Sudah Dibaca</div>
            <div class="dk-heading" style="font-size:22px; font-weight:700;">
                {{ number_format($readCount) }}
                <span class="dk-text-muted" style="font-size:14px; font-weight:400;">/ {{ number_format($broadcast->recipients_count) }}</span>
            </div>
            <div class="dk-text-muted" style="font-size:12px;">
                {{ $broadcast->recipients_count > 0 ? number_format($readCount * 100 / $broadcast->recipients_count, 1) : 0 }}%
            </div>
        </div>
    </div>
</div>

<div class="dk-card" style="padding:24px; margin-bottom:24px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <h2 class="dk-heading" style="font-size:16px; font-weight:600;">Isi Pesan</h2>
        @if($broadcast->admin)
            <span class="dk-text-muted" style="font-size:13px;">oleh {{ $broadcast->admin->name }}</span>
        @endif
    </div>

    @if($broadcast->body)
        <div class="dk-text" style="white-space:pre-wrap; line-height:1.6;">{{ $broadcast->body }}</div>
    @else
        <div class="dk-text-muted" style="font-style:italic;">(tidak ada teks)</div>
    @endif

    @if($broadcast->hasAttachment())
        <div style="margin-top:16px;">
            @if($broadcast->isImage())
                <a href="{{ route('admin.broadcasts.attachment', $broadcast) }}" target="_blank">
                    <img src="{{ route('admin.broadcasts.attachment', $broadcast) }}" alt="{{ $broadcast->attachment_name }}" style="max-width:320px; max-height:320px; border-radius:8px; display:block;">
                </a>
            @else
                <a href="{{ route('admin.broadcasts.attachment', $broadcast) }}" class="dk-btn dk-btn-outline" style="padding:8px 14px;">
                    <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    {{ $broadcast->attachment_name }} ({{ $broadcast->attachmentSizeHuman() }})
                </a>
            @endif
        </div>
    @endif
</div>

<div class="dk-card" style="padding:24px;">
    <h2 class="dk-heading" style="font-size:16px; font-weight:600; margin-bottom:16px;">Daftar Penerima</h2>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid #2d3a4a;">
                    <th style="text-align:left; padding:8px 12px; color:#94a3b8; font-weight:600;">Member</th>
                    <th style="text-align:left; padding:8px 12px; color:#94a3b8; font-weight:600;">Status Dibaca</th>
                    <th style="text-align:right; padding:8px 12px; color:#94a3b8; font-weight:600;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recipients as $r)
                    <tr style="border-bottom:1px solid rgba(45,58,74,0.5);">
                        <td class="dk-text" style="padding:10px 12px;">
                            @if($r->user)
                                <div style="font-weight:600;">{{ $r->user->name }}</div>
                                <div class="dk-text-muted" style="font-size:12px;">{{ $r->user->email }}</div>
                            @else
                                <span class="dk-text-muted">— (member terhapus)</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;">
                            @if($r->read_at)
                                <span class="dk-badge" style="background:rgba(16,185,129,0.15); color:#6ee7b7;">Dibaca {{ $r->read_at->diffForHumans() }}</span>
                            @else
                                <span class="dk-badge" style="background:rgba(245,158,11,0.15); color:#fbbf24;">Belum dibaca</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px; text-align:right;">
                            @if($r->user)
                                <a href="{{ route('admin.messages.show', $r->user) }}" class="dk-btn dk-btn-outline" style="padding:4px 10px; font-size:12px;">Lihat Thread</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; padding:16px;" class="dk-text-muted">Tidak ada penerima.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($recipients->hasPages())
        <div style="margin-top:16px;">{{ $recipients->links() }}</div>
    @endif
</div>
@endsection
