@extends('layouts.dashboard')
@section('title', 'Video Tutorial - ' . $product->title)

@section('content')
<div style="margin-bottom:24px">
    <a href="{{ route('dashboard.video-tutorials') }}" style="color:#818cf8;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:4px">
        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar
    </a>
</div>

<h1 class="text-2xl font-bold dk-heading mb-2">{{ $product->title }}</h1>
<p class="dk-text-muted mb-6">{{ $tutorials->count() }} video tutorial tersedia</p>

@if($tutorials->count() > 0)
    <div class="space-y-6">
        @foreach($tutorials as $index => $tutorial)
            <div class="dk-card" style="padding:24px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                    <span style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-size:12px;font-weight:700;padding:4px 10px;border-radius:9999px">{{ $index + 1 }}</span>
                    <h2 style="font-size:18px;font-weight:600;color:#e2e8f0">{{ $tutorial->title }}</h2>
                </div>

                @if($tutorial->description)
                    <p style="font-size:14px;color:#94a3b8;margin-bottom:16px">{{ $tutorial->description }}</p>
                @endif

                @if($tutorial->embed_url)
                    <div style="max-width:720px">
                        <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:12px;overflow:hidden;background:#0b1120">
                            <iframe src="{{ $tutorial->embed_url }}" style="position:absolute;top:0;left:0;width:100%;height:100%" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                @else
                    <a href="{{ $tutorial->video_url }}" target="_blank" class="dk-btn dk-btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Tonton Video
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="dk-card" style="padding:48px;text-align:center">
        <svg style="width:48px;height:48px;color:#64748b;margin:0 auto 12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
        <p class="dk-text-muted">Belum ada video tutorial untuk produk ini.</p>
    </div>
@endif
@endsection
