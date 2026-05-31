@extends('layouts.admin')
@section('title', 'Webhook Delivery #' . $delivery->id)

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Webhook Delivery #{{ $delivery->id }}</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">
            Event <code>{{ $delivery->event }}</code> untuk produk <strong>{{ $product->title }}</strong>.
        </p>
    </div>
    <a href="{{ route('admin.products.webhook-deliveries', $product) }}" class="dk-btn dk-btn-outline">Kembali</a>
</div>

@if(session('success'))<div class="dk-alert dk-alert-success mb-4">{{ session('success') }}</div>@endif
@if(session('error'))<div class="dk-alert dk-alert-error mb-4">{{ session('error') }}</div>@endif

<div class="dk-card" style="padding:24px;">
    <dl style="display:grid; grid-template-columns:max-content 1fr; gap:12px 24px;">
        <dt class="dk-text-muted">Status</dt>
        <dd>
            @if($delivery->result === 'success')
                <span class="dk-badge dk-badge-success">{{ $delivery->status_code }} OK</span>
            @else
                <span class="dk-badge dk-badge-danger">{{ $delivery->status_code ? $delivery->status_code.' FAIL' : 'ERROR' }}</span>
            @endif
        </dd>

        <dt class="dk-text-muted">Attempt</dt>
        <dd>#{{ $delivery->attempt }}</dd>

        <dt class="dk-text-muted">URL</dt>
        <dd><code>{{ $delivery->url }}</code></dd>

        <dt class="dk-text-muted">License</dt>
        <dd>{{ $delivery->license?->key ?? '—' }}</dd>

        <dt class="dk-text-muted">Dibuat</dt>
        <dd>{{ optional($delivery->created_at)->format('d M Y H:i:s') }}</dd>

        <dt class="dk-text-muted">Dikirim</dt>
        <dd>{{ optional($delivery->delivered_at)->format('d M Y H:i:s') ?? '—' }}</dd>

        @if($delivery->signature)
        <dt class="dk-text-muted">Signature</dt>
        <dd><code style="word-break:break-all;">{{ $delivery->signature }}</code></dd>
        @endif

        @if($delivery->error_message)
        <dt class="dk-text-muted">Error</dt>
        <dd style="color:#fca5a5;">{{ $delivery->error_message }}</dd>
        @endif
    </dl>
</div>

<div class="dk-card" style="padding:24px; margin-top:16px;">
    <h3 class="text-sm font-semibold dk-heading mb-2">Payload (request body)</h3>
    <pre style="background:rgba(0,0,0,0.3); padding:12px; border-radius:6px; overflow-x:auto; font-size:12px;">{{ json_encode($delivery->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
</div>

@if($delivery->response_body)
<div class="dk-card" style="padding:24px; margin-top:16px;">
    <h3 class="text-sm font-semibold dk-heading mb-2">Response body</h3>
    <pre style="background:rgba(0,0,0,0.3); padding:12px; border-radius:6px; overflow-x:auto; font-size:12px;">{{ $delivery->response_body }}</pre>
</div>
@endif

@if($delivery->result === 'failed')
<div style="margin-top:24px;">
    <form action="{{ route('admin.products.webhook-deliveries.retry', [$product, $delivery]) }}" method="POST">
        @csrf
        <button type="submit" class="dk-btn dk-btn-primary">Retry Delivery</button>
    </form>
</div>
@endif
@endsection
