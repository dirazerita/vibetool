@extends('layouts.dashboard')
@section('title', 'Detail Request: ' . $softwareRequest->title)

@section('content')
<style>
    .sr-detail-section { background:#0f1729; border:1px solid #1e2b3d; border-radius:14px; padding:20px; margin-bottom:16px; }
    .sr-detail-section h3 { font-size:13px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; }
    .sr-detail-section p { color:#e2e8f0; font-size:14px; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
    .sr-status-pill { display:inline-block; padding:4px 14px; border-radius:9999px; font-size:13px; font-weight:600; color:#fff; }
    .sr-platform-tag { display:inline-block; padding:3px 10px; border-radius:9999px; background:rgba(99,102,241,0.15); color:#a5b4fc; font-size:12px; font-weight:600; margin:2px 4px 2px 0; }
    .sr-meta-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; }
    .sr-meta-item { font-size:13px; }
    .sr-meta-item dt { color:#64748b; margin-bottom:4px; }
    .sr-meta-item dd { color:#cbd5e1; font-weight:500; }
    .sr-attachment-link { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; background:#151e2d; border:1px solid #2d3a4a; border-radius:10px; color:#cbd5e1; text-decoration:none; font-size:13px; }
    .sr-attachment-link:hover { border-color:#475569; }
    .sr-response-card { background:linear-gradient(135deg, rgba(79,70,229,0.1), rgba(124,58,237,0.05)); border:1px solid rgba(99,102,241,0.3); border-radius:14px; padding:20px; margin-bottom:16px; }
    .sr-response-header { display:flex; align-items:center; gap:8px; margin-bottom:12px; font-size:13px; font-weight:600; color:#a5b4fc; }
    .sr-response-body { color:#e2e8f0; font-size:14px; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
    .sr-response-meta { font-size:12px; color:#64748b; margin-top:10px; }
    @media (max-width: 720px) {
        .sr-detail-section { padding:16px; }
    }
</style>

<div style="margin-bottom:24px;">
    <a href="{{ route('dashboard.software-requests.index') }}" style="color:#94a3b8; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-bottom:12px;">
        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar
    </a>
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <div>
            <h1 class="text-2xl font-bold dk-heading">{{ $softwareRequest->title }}</h1>
            <p class="dk-text-muted" style="font-size:13px; margin-top:4px;">
                Dikirim {{ $softwareRequest->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}
            </p>
        </div>
        <span class="sr-status-pill" style="background:{{ $softwareRequest->statusColor() }};">{{ $softwareRequest->statusLabel() }}</span>
    </div>
</div>

@if($softwareRequest->admin_response)
    <div class="sr-response-card">
        <div class="sr-response-header">
            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Respon dari Admin
        </div>
        <div class="sr-response-body">{{ $softwareRequest->admin_response }}</div>
        @if($softwareRequest->admin_responded_at)
            <div class="sr-response-meta">{{ $softwareRequest->admin_responded_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</div>
        @endif
        @if($softwareRequest->product)
            <div style="margin-top:14px;">
                <a href="{{ route('product.show', $softwareRequest->product->slug) }}" target="_blank" class="dk-btn dk-btn-primary">
                    Lihat Produk: {{ $softwareRequest->product->title }}
                </a>
            </div>
        @endif
    </div>
@endif

<div class="sr-detail-section">
    <h3>Apa gunanya?</h3>
    <p>{{ $softwareRequest->purpose }}</p>
</div>

<div class="sr-detail-section">
    <h3>Siapa yang akan pakai?</h3>
    <p>{{ $softwareRequest->target_users }}</p>
</div>

<div class="sr-detail-section">
    <h3>Masalah yang ingin diselesaikan</h3>
    <p>{{ $softwareRequest->problem_to_solve }}</p>
</div>

@if($softwareRequest->similar_apps)
    <div class="sr-detail-section">
        <h3>Aplikasi sejenis</h3>
        <p>{{ $softwareRequest->similar_apps }}</p>
    </div>
@endif

<div class="sr-detail-section">
    <h3>Platform</h3>
    <div>
        @foreach($softwareRequest->platformLabels() as $label)
            <span class="sr-platform-tag">{{ $label }}</span>
        @endforeach
    </div>
</div>

<div class="sr-detail-section">
    <h3>Fitur penting</h3>
    <p>{{ $softwareRequest->key_features }}</p>
</div>

@if($softwareRequest->hasAttachment())
    <div class="sr-detail-section">
        <h3>Lampiran</h3>
        <a href="{{ route('dashboard.software-requests.attachment', $softwareRequest) }}" class="sr-attachment-link" target="_blank">
            <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            <span>{{ $softwareRequest->attachment_name }} ({{ $softwareRequest->attachmentSizeHuman() }})</span>
        </a>
    </div>
@endif

@if($softwareRequest->budget_range || $softwareRequest->urgency)
    <div class="sr-detail-section">
        <h3>Detail tambahan</h3>
        <dl class="sr-meta-grid">
            @if($softwareRequest->budget_range)
                <div class="sr-meta-item">
                    <dt>Estimasi budget</dt>
                    <dd>{{ $softwareRequest->budgetLabel() }}</dd>
                </div>
            @endif
            @if($softwareRequest->urgency)
                <div class="sr-meta-item">
                    <dt>Kapan butuh</dt>
                    <dd>{{ $softwareRequest->urgencyLabel() }}</dd>
                </div>
            @endif
        </dl>
    </div>
@endif

@if($softwareRequest->additional_notes)
    <div class="sr-detail-section">
        <h3>Catatan tambahan</h3>
        <p>{{ $softwareRequest->additional_notes }}</p>
    </div>
@endif
@endsection
