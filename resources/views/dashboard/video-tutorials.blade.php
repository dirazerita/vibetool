@extends('layouts.dashboard')
@section('title', 'Video Tutorial')

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Video Tutorial</h1>
<p class="dk-text-muted mb-6">Pelajari cara menggunakan produk dengan menonton video tutorial berikut.</p>

@if($products->count() > 0)
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px">
        @foreach($products as $product)
            <a href="{{ route('dashboard.video-tutorials.show', $product) }}" class="dk-card" style="padding:0;text-decoration:none;transition:all 0.2s;overflow:hidden;display:block" onmouseover="this.style.borderColor='#4f46e5';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#2d3a4a';this.style.transform='none'">
                @if($product->thumbnail)
                    <div style="height:160px;overflow:hidden;background:#151e2d">
                        <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->title }}" style="width:100%;height:100%;object-fit:cover">
                    </div>
                @else
                    <div style="height:160px;background:linear-gradient(135deg,#1e1b4b,#312e81);display:flex;align-items:center;justify-content:center">
                        <svg style="width:48px;height:48px;color:#6366f1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
                <div style="padding:16px">
                    <h3 style="font-size:16px;font-weight:600;color:#e2e8f0;margin-bottom:4px">{{ $product->title }}</h3>
                    <p style="font-size:13px;color:#94a3b8">{{ $product->videoTutorials->count() }} video tutorial</p>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="dk-card" style="padding:48px;text-align:center">
        <svg style="width:48px;height:48px;color:#64748b;margin:0 auto 12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
        <p class="dk-text-muted">Belum ada video tutorial yang tersedia saat ini.</p>
    </div>
@endif
@endsection
