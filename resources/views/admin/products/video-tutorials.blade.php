@extends('layouts.admin')
@section('title', 'Video Tutorial - ' . $product->title)

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Edit Produk: {{ $product->title }}</h1>

<div class="mb-6 " style="border-bottom:1px solid #1e2b3d">
    <nav class="flex space-x-8">
        <a href="{{ route('admin.products.edit', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Produk</a>
        <a href="{{ route('admin.products.landing-page', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Landing Page</a>
        <a href="{{ route('admin.products.video-tutorials', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid #6366f1;color:#a5b4fc">Video Tutorial</a>
    </nav>
</div>

@if(session('success'))
    <div class="dk-alert-success">{{ session('success') }}</div>
@endif

{{-- Add New Tutorial --}}
<div class="max-w-3xl mb-8">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Tambah Video Tutorial</h2>
        <form method="POST" action="{{ route('admin.products.video-tutorials.store', $product) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="title" class="dk-label">Judul Video</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full dk-input" required placeholder="Contoh: Cara Install Software">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="video_url" class="dk-label">URL Video (YouTube)</label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}" class="w-full dk-input" required placeholder="https://www.youtube.com/watch?v=...">
                    <p class="text-xs mt-1 dk-text-muted">Mendukung format YouTube: youtube.com/watch?v=..., youtu.be/..., atau embed URL.</p>
                    @error('video_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="dk-label">Deskripsi (Opsional)</label>
                    <textarea name="description" id="description" rows="3" class="w-full dk-input" placeholder="Penjelasan singkat tentang isi video ini...">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="dk-btn dk-btn-primary">
                    <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Video
                </button>
            </div>
        </form>
    </div>
</div>

{{-- List Tutorials --}}
<div class="max-w-3xl mb-8">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Daftar Video Tutorial ({{ $tutorials->count() }})</h2>

        @if($tutorials->count() > 0)
            <div class="space-y-4">
                @foreach($tutorials as $index => $tutorial)
                    <div class="rounded-lg p-4" style="border:1px solid #2d3a4a;background:#151e2d" x-data="{ editing: false }">
                        <div x-show="!editing">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
                                <div style="flex:1;min-width:0">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                                        <span style="font-size:12px;color:#64748b;font-weight:600">#{{ $index + 1 }}</span>
                                        <h3 style="font-size:16px;font-weight:600;color:#e2e8f0">{{ $tutorial->title }}</h3>
                                        @if($tutorial->is_active)
                                            <span class="dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7">Aktif</span>
                                        @else
                                            <span class="dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5">Nonaktif</span>
                                        @endif
                                    </div>
                                    @if($tutorial->description)
                                        <p style="font-size:13px;color:#94a3b8;margin-bottom:8px">{{ $tutorial->description }}</p>
                                    @endif
                                    <p style="font-size:12px;color:#64748b">
                                        <svg style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                        {{ Str::limit($tutorial->video_url, 60) }}
                                    </p>
                                </div>
                                <div style="display:flex;gap:6px;flex-shrink:0">
                                    <button @click="editing = true" class="dk-btn dk-btn-outline" style="padding:6px 12px;font-size:12px">Edit</button>
                                    <form method="POST" action="{{ route('admin.products.video-tutorials.toggle', [$product, $tutorial]) }}">
                                        @csrf
                                        <button type="submit" class="dk-btn {{ $tutorial->is_active ? 'dk-btn-warning' : 'dk-btn-success' }}" style="padding:6px 12px;font-size:12px">
                                            {{ $tutorial->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.video-tutorials.destroy', [$product, $tutorial]) }}" onsubmit="return confirm('Hapus video tutorial ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dk-btn dk-btn-danger" style="padding:6px 12px;font-size:12px">Hapus</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Video Preview --}}
                            @if($tutorial->embed_url)
                                <div style="margin-top:12px;max-width:480px">
                                    <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:8px;overflow:hidden;background:#0b1120">
                                        <iframe src="{{ $tutorial->embed_url }}" style="position:absolute;top:0;left:0;width:100%;height:100%" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Edit Form --}}
                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('admin.products.video-tutorials.update', [$product, $tutorial]) }}">
                                @csrf @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="dk-label">Judul Video</label>
                                        <input type="text" name="title" value="{{ $tutorial->title }}" class="w-full dk-input" required>
                                    </div>
                                    <div>
                                        <label class="dk-label">URL Video</label>
                                        <input type="url" name="video_url" value="{{ $tutorial->video_url }}" class="w-full dk-input" required>
                                    </div>
                                    <div>
                                        <label class="dk-label">Deskripsi</label>
                                        <textarea name="description" rows="3" class="w-full dk-input">{{ $tutorial->description }}</textarea>
                                    </div>
                                    <div style="display:flex;gap:8px">
                                        <button type="submit" class="dk-btn dk-btn-primary" style="padding:6px 16px;font-size:13px">Simpan</button>
                                        <button type="button" @click="editing = false" class="dk-btn dk-btn-outline" style="padding:6px 16px;font-size:13px">Batal</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:32px 0">
                <svg style="width:48px;height:48px;color:#64748b;margin:0 auto 12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <p class="dk-text-muted">Belum ada video tutorial untuk produk ini.</p>
                <p class="text-xs dk-text-muted" style="margin-top:4px">Tambahkan video tutorial di atas agar member bisa belajar cara menggunakan produk ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection
