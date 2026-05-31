@extends('layouts.admin')
@section('title', 'Webhook Deliveries — ' . $product->title)

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 class="text-2xl font-bold dk-heading">Webhook Deliveries</h1>
        <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">
            Riwayat pengiriman webhook untuk produk <strong>{{ $product->title }}</strong>.
            URL aktif: <code>{{ $product->webhook_url ?? '(belum di-set)' }}</code>
        </p>
    </div>
    <a href="{{ route('admin.products.edit', $product) }}" class="dk-btn dk-btn-outline">Edit Produk</a>
</div>

@if(session('success'))<div class="dk-alert dk-alert-success mb-4">{{ session('success') }}</div>@endif
@if(session('error'))<div class="dk-alert dk-alert-error mb-4">{{ session('error') }}</div>@endif

@if($deliveries->isEmpty())
    <div class="dk-card" style="padding:24px; text-align:center;">
        <p class="dk-text-muted">Belum ada webhook yang dikirim. Webhook akan otomatis dikirim saat ada event <code>license.issued</code>, <code>license.revoked</code>, atau <code>license.renewed</code>.</p>
    </div>
@else
<div class="dk-card" style="overflow-x:auto;">
    <table class="w-full text-sm">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid rgba(255,255,255,0.1);">
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">Event</th>
                <th style="padding:12px;">License</th>
                <th style="padding:12px;">Status</th>
                <th style="padding:12px;">Attempt</th>
                <th style="padding:12px;">Dikirim</th>
                <th style="padding:12px;"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($deliveries as $d)
            <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                <td style="padding:12px;">{{ $d->id }}</td>
                <td style="padding:12px;"><code>{{ $d->event }}</code></td>
                <td style="padding:12px;">
                    @if($d->license)
                        <code>{{ $d->license->key }}</code>
                    @else
                        <span class="dk-text-muted">—</span>
                    @endif
                </td>
                <td style="padding:12px;">
                    @if($d->result === 'success')
                        <span class="dk-badge dk-badge-success">{{ $d->status_code }} OK</span>
                    @else
                        <span class="dk-badge dk-badge-danger">{{ $d->status_code ? $d->status_code.' FAIL' : 'ERROR' }}</span>
                    @endif
                </td>
                <td style="padding:12px;">#{{ $d->attempt }}</td>
                <td style="padding:12px;">
                    <span class="dk-text-muted" style="font-size:12px;">
                        {{ optional($d->delivered_at)->diffForHumans() ?? optional($d->created_at)->diffForHumans() }}
                    </span>
                </td>
                <td style="padding:12px;">
                    <a href="{{ route('admin.products.webhook-deliveries.show', [$product, $d]) }}" class="dk-btn dk-btn-outline text-xs">Detail</a>
                    @if($d->result === 'failed')
                        <form action="{{ route('admin.products.webhook-deliveries.retry', [$product, $d]) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="dk-btn dk-btn-primary text-xs">Retry</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $deliveries->links() }}</div>
@endif
@endsection
