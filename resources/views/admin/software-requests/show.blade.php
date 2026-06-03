@extends('layouts.admin')
@section('title', 'Detail Request: ' . $softwareRequest->title)

@section('content')
<style>
    .sr-grid { display:grid; grid-template-columns: 2fr 1fr; gap:20px; }
    .sr-section { background:#0f1729; border:1px solid #1e2b3d; border-radius:14px; padding:20px; margin-bottom:16px; }
    .sr-section h3 { font-size:12px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; }
    .sr-section p { color:#e2e8f0; font-size:14px; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
    .sr-status-pill { display:inline-block; padding:4px 14px; border-radius:9999px; font-size:13px; font-weight:600; color:#fff; }
    .sr-platform-tag { display:inline-block; padding:3px 10px; border-radius:9999px; background:rgba(99,102,241,0.15); color:#a5b4fc; font-size:12px; font-weight:600; margin:2px 4px 2px 0; }
    .sr-meta dt { color:#64748b; font-size:12px; margin-bottom:4px; }
    .sr-meta dd { color:#cbd5e1; font-size:14px; font-weight:500; margin-bottom:12px; }
    .sr-input, .sr-textarea, .sr-select { width:100%; background:#151e2d; border:1px solid #2d3a4a; color:#e2e8f0; padding:9px 11px; border-radius:8px; font-size:14px; font-family:inherit; }
    .sr-input:focus, .sr-textarea:focus, .sr-select:focus { outline:none; border-color:#6366f1; }
    .sr-textarea { resize:vertical; min-height:80px; }
    .sr-form-label { display:block; font-weight:600; color:#e2e8f0; font-size:13px; margin-bottom:6px; margin-top:14px; }
    .sr-form-help { font-size:12px; color:#94a3b8; margin-top:4px; line-height:1.5; }
    .sr-attachment-link { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; background:#151e2d; border:1px solid #2d3a4a; border-radius:10px; color:#cbd5e1; text-decoration:none; font-size:13px; }
    .sr-attachment-link:hover { border-color:#475569; }
    .sr-member-card { display:block; background:#151e2d; border:1px solid #2d3a4a; border-radius:10px; padding:14px; text-decoration:none; color:inherit; }
    .sr-member-card:hover { border-color:#475569; }
    .sr-member-card .name { font-weight:600; color:#e2e8f0; font-size:15px; }
    .sr-member-card .meta { font-size:12px; color:#94a3b8; margin-top:4px; }
    @media (max-width: 1024px) { .sr-grid { grid-template-columns: 1fr; } }
</style>

<div style="margin-bottom:20px;">
    <a href="{{ route('admin.software-requests.index') }}" style="color:#94a3b8; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-bottom:12px;">
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

@if(session('success'))
    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:14px;">
        {{ session('success') }}
    </div>
@endif

<div class="sr-grid">
    {{-- LEFT --}}
    <div>
        <div class="sr-section">
            <h3>Apa gunanya?</h3>
            <p>{{ $softwareRequest->purpose }}</p>
        </div>

        <div class="sr-section">
            <h3>Siapa yang akan pakai?</h3>
            <p>{{ $softwareRequest->target_users }}</p>
        </div>

        <div class="sr-section">
            <h3>Masalah yang ingin diselesaikan</h3>
            <p>{{ $softwareRequest->problem_to_solve }}</p>
        </div>

        @if($softwareRequest->similar_apps)
            <div class="sr-section">
                <h3>Aplikasi sejenis yang member tahu</h3>
                <p>{{ $softwareRequest->similar_apps }}</p>
            </div>
        @endif

        <div class="sr-section">
            <h3>Platform</h3>
            <div>
                @foreach($softwareRequest->platformLabels() as $label)
                    <span class="sr-platform-tag">{{ $label }}</span>
                @endforeach
            </div>
        </div>

        <div class="sr-section">
            <h3>Fitur penting</h3>
            <p>{{ $softwareRequest->key_features }}</p>
        </div>

        @if($softwareRequest->hasAttachment())
            <div class="sr-section">
                <h3>Lampiran</h3>
                <a href="{{ route('admin.software-requests.attachment', $softwareRequest) }}" class="sr-attachment-link" target="_blank">
                    <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>{{ $softwareRequest->attachment_name }} ({{ $softwareRequest->attachmentSizeHuman() }})</span>
                </a>
            </div>
        @endif

        @if($softwareRequest->additional_notes)
            <div class="sr-section">
                <h3>Catatan tambahan dari member</h3>
                <p>{{ $softwareRequest->additional_notes }}</p>
            </div>
        @endif
    </div>

    {{-- RIGHT --}}
    <div>
        <div class="sr-section">
            <h3>Member yang Mengirim</h3>
            <a href="{{ route('admin.members.edit', $softwareRequest->user) }}" class="sr-member-card">
                <div class="name">{{ $softwareRequest->user->name }}</div>
                <div class="meta">{{ $softwareRequest->user->email }}</div>
                @if($softwareRequest->user->whatsapp_number)
                    <div class="meta">WA: {{ $softwareRequest->user->whatsapp_number }}</div>
                @endif
            </a>
            <div style="margin-top:10px;">
                <a href="{{ route('admin.messages.show', $softwareRequest->user) }}" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center;">
                    <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Chat Langsung
                </a>
            </div>
        </div>

        <div class="sr-section">
            <h3>Detail tambahan</h3>
            <dl class="sr-meta">
                <dt>Estimasi budget</dt>
                <dd>{{ $softwareRequest->budgetLabel() ?? '—' }}</dd>
                <dt>Kapan butuh</dt>
                <dd>{{ $softwareRequest->urgencyLabel() ?? '—' }}</dd>
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.software-requests.update', $softwareRequest) }}" class="sr-section">
            @csrf
            @method('PUT')
            <h3 style="margin-bottom:0;">Update Status & Respon</h3>

            <label class="sr-form-label" for="status">Status</label>
            <select id="status" name="status" class="sr-select">
                @foreach(\App\Models\SoftwareRequest::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $softwareRequest->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <p style="color:#fca5a5; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror

            <label class="sr-form-label" for="admin_response">Respon untuk member</label>
            <p class="sr-form-help">Member akan lihat ini di halaman detail request mereka. Kosongkan kalau belum mau balas.</p>
            <textarea id="admin_response" name="admin_response" class="sr-textarea" rows="4" maxlength="5000" placeholder="Misal: 'Diterima, kami akan kerjakan dalam 2 minggu ke depan.'">{{ old('admin_response', $softwareRequest->admin_response) }}</textarea>
            @error('admin_response') <p style="color:#fca5a5; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror

            <label class="sr-form-label" for="admin_notes">Catatan internal (admin saja)</label>
            <p class="sr-form-help">Hanya admin yang lihat. Untuk koordinasi tim — estimasi waktu, requirement teknis, catatan dev.</p>
            <textarea id="admin_notes" name="admin_notes" class="sr-textarea" rows="4" maxlength="10000" placeholder="Catatan internal...">{{ old('admin_notes', $softwareRequest->admin_notes) }}</textarea>
            @error('admin_notes') <p style="color:#fca5a5; font-size:12px; margin-top:4px;">{{ $message }}</p> @enderror

            <label class="sr-form-label" for="product_id">Link ke Produk <span style="color:#64748b; font-weight:400; font-size:11px;">(opsional)</span></label>
            <p class="sr-form-help">Kalau request ini sudah jadi produk, link ke produk-nya. Member akan lihat tombol "Lihat Produk".</p>
            <select id="product_id" name="product_id" class="sr-select">
                <option value="">— Tidak ada —</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id', $softwareRequest->product_id) == $product->id)>{{ $product->title }}</option>
                @endforeach
            </select>

            <button type="submit" class="dk-btn dk-btn-primary" style="margin-top:16px; width:100%; justify-content:center;">
                Simpan Perubahan
            </button>
        </form>

        <form method="POST" action="{{ route('admin.software-requests.destroy', $softwareRequest) }}" onsubmit="return confirm('Yakin hapus request ini? Tidak bisa di-undo.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center; color:#fca5a5; border-color:rgba(239,68,68,0.3);">
                Hapus Request
            </button>
        </form>
    </div>
</div>
@endsection
