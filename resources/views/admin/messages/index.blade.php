@extends('layouts.admin')
@section('title', 'Pesan')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Pesan Member</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Daftar percakapan dengan member. Klik untuk membuka thread &amp; membalas.</p>
    </div>
    <form method="GET" action="{{ route('admin.messages.index') }}" style="display:flex; gap:8px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email/WA" class="dk-input" style="width:240px;">
        <button type="submit" class="dk-btn dk-btn-outline">Cari</button>
        @if(request('search'))
            <a href="{{ route('admin.messages.index') }}" class="dk-btn dk-btn-outline">Reset</a>
        @endif
    </form>
</div>

<div class="dk-table">
    <table style="width:100%;">
        <thead>
            <tr>
                <th>Member</th>
                <th>Pesan Terakhir</th>
                <th>Waktu</th>
                <th>Belum Dibaca</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conversations as $c)
                @php $lastAt = $c->last_at ? \Illuminate\Support\Carbon::parse($c->last_at) : null; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; color:#e2e8f0;">{{ $c->name }}</div>
                        <div class="dk-text-muted" style="font-size:12px;">{{ $c->email }}</div>
                    </td>
                    <td style="max-width:360px;">
                        @if($c->last_sender_role === 'admin')
                            <span class="dk-text-muted" style="font-size:12px;">Anda: </span>
                        @endif
                        @if($c->last_body)
                            <span style="color:#cbd5e1;">{{ \Illuminate\Support\Str::limit($c->last_body, 80) }}</span>
                        @elseif($c->last_attachment_path)
                            <span class="dk-text-muted" style="font-style:italic;">[Lampiran]</span>
                        @else
                            <span class="dk-text-muted">—</span>
                        @endif
                    </td>
                    <td class="dk-text-muted" style="font-size:12px; white-space:nowrap;">
                        {{ $lastAt ? $lastAt->diffForHumans() : '—' }}
                    </td>
                    <td>
                        @if(((int) $c->unread_count) > 0)
                            <span class="dk-badge" style="background:#ef4444; color:#fff;">{{ (int) $c->unread_count }} baru</span>
                        @else
                            <span class="dk-text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.messages.show', $c->id) }}" class="dk-btn dk-btn-primary" style="padding:6px 14px;">Buka</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:32px;" class="dk-text-muted">Belum ada percakapan dari member.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($conversations->hasPages())
    <div style="margin-top:16px;">{{ $conversations->links() }}</div>
@endif
@endsection
