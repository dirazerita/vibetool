@extends('layouts.admin')
@section('title', 'Landing Page - ' . $product->title)

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">Edit Produk: {{ $product->title }}</h1>

<div class="mb-6 " style="border-bottom:1px solid #1e2b3d">
    <nav class="flex space-x-8">
        <a href="{{ route('admin.products.edit', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Produk</a>
        <a href="{{ route('admin.products.landing-page', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid #6366f1;color:#a5b4fc">Landing Page</a>
        <a href="{{ route('admin.products.video-tutorials', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Video Tutorial</a>
    </nav>
</div>

@php $lp = $product->landingPage; @endphp

<div class="max-w-3xl mb-6">
    <div class="dk-card" style="padding:14px 18px;border-left:4px solid #6366f1;background:rgba(99,102,241,0.08)">
        <p class="text-sm dk-text" style="line-height:1.6">
            <strong style="color:#a5b4fc">Catatan:</strong> Halaman ini punya 3 section yang disimpan terpisah —
            <strong>Konten Utama</strong> (hero/video/about/publish), <strong>Galeri Gambar</strong>, dan <strong>Testimonial</strong>.
            Setiap section punya tombol simpannya sendiri. Setelah upload gambar atau tambah testimonial, item akan muncul di list section masing-masing.
        </p>
    </div>
</div>

{{-- Hero & Video & About Section --}}
<div class="max-w-3xl mb-8">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Konten Utama Landing Page</h2>
        <form method="POST" action="{{ route('admin.products.landing-page.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-6">
                {{-- Hero Section --}}
                <div style="border-bottom:1px solid #1e2b3d pb-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 dk-text">Hero Section</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="hero_title" class="dk-label">Judul Utama</label>
                            <input type="text" name="hero_title" id="hero_title" value="{{ old('hero_title', $lp->hero_title ?? $product->title) }}" class="w-full dk-input" required>
                            @error('hero_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="hero_subtitle" class="dk-label">Subjudul</label>
                            <input type="text" name="hero_subtitle" id="hero_subtitle" value="{{ old('hero_subtitle', $lp->hero_subtitle ?? '') }}" class="w-full dk-input">
                            @error('hero_subtitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="hero_title_font" class="block text-xs font-medium dk-text mb-1">Font Judul</label>
                                <select name="hero_title_font" id="hero_title_font" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach(['Sans-serif', 'Serif', 'Monospace', 'Poppins', 'Playfair Display', 'Roboto'] as $font)
                                        <option value="{{ $font }}" {{ old('hero_title_font', $lp->hero_title_font ?? 'Poppins') == $font ? 'selected' : '' }}>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="hero_title_size" class="block text-xs font-medium dk-text mb-1">Ukuran Judul</label>
                                <select name="hero_title_size" id="hero_title_size" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach(['32px', '40px', '48px', '56px', '64px'] as $size)
                                        <option value="{{ $size }}" {{ old('hero_title_size', $lp->hero_title_size ?? '48px') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="hero_title_color" class="block text-xs font-medium dk-text mb-1">Warna Judul</label>
                                <input type="color" name="hero_title_color" id="hero_title_color" value="{{ old('hero_title_color', $lp->hero_title_color ?? '#ffffff') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                            </div>
                        </div>
                        <div class="dk-grid-2 gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                            <div>
                                <label for="hero_subtitle_font" class="block text-xs font-medium dk-text mb-1">Font Subjudul</label>
                                <select name="hero_subtitle_font" id="hero_subtitle_font" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach(['Sans-serif', 'Serif', 'Monospace', 'Poppins', 'Playfair Display', 'Roboto'] as $font)
                                        <option value="{{ $font }}" {{ old('hero_subtitle_font', $lp->hero_subtitle_font ?? 'Poppins') == $font ? 'selected' : '' }}>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="hero_subtitle_color" class="block text-xs font-medium dk-text mb-1">Warna Subjudul</label>
                                <input type="color" name="hero_subtitle_color" id="hero_subtitle_color" value="{{ old('hero_subtitle_color', $lp->hero_subtitle_color ?? '#e2e8f0') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label for="hero_image" class="dk-label">Gambar Hero</label>
                            @if($lp && $lp->hero_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $lp->hero_image) }}" alt="Hero" class="w-full rounded-lg object-cover" style="max-height: 200px;">
                                </div>
                            @endif
                            <input type="file" name="hero_image" id="hero_image" accept="image/*" class="w-full dk-input">
                            <p class="text-xs mt-1 dk-text-muted">Maks 5MB. Kosongkan jika tidak ingin mengubah.</p>
                            @error('hero_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Video Section --}}
                <div style="border-bottom:1px solid #1e2b3d pb-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 dk-text">Video Section</h3>
                    <div>
                        <label for="video_url" class="dk-label">URL Video (YouTube/Vimeo)</label>
                        <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $lp->video_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=..." class="w-full dk-input">
                        @error('video_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @if($lp && $lp->video_url)
                            <div class="mt-3" style="max-width: 560px;">
                                <p class="text-xs dk-text-muted mb-1">Preview:</p>
                                <div class="rounded-lg overflow-hidden " style="background:#151e2d;position: relative; padding-bottom: 56.25%; height: 0;">
                                    <iframe src="{{ \App\Helpers\VideoHelper::getEmbedUrl($lp->video_url) }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" frameborder="0" allowfullscreen></iframe>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- About Content --}}
                <div style="border-bottom:1px solid #1e2b3d pb-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 dk-text">Deskripsi Detail</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="about_content" class="dk-label">Konten tentang produk</label>
                            <textarea name="about_content" id="about_content" rows="15" class="w-full dk-input">{{ old('about_content', $lp->about_content ?? '') }}</textarea>
                            <p class="text-xs mt-1 dk-text-muted">
                                Pakai toolbar editor di atas untuk format teks (heading, list, link, gambar, dll). Paste dari Word / Google Docs / ChatGPT juga jalan.
                                Konten lama yang masih mentah (mis. <code>#</code>, <code>###</code>) tampil sebagai teks biasa &mdash; hapus lalu re-format pakai toolbar.
                            </p>
                            @error('about_content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label for="about_font" class="block text-xs font-medium dk-text mb-1">Font Deskripsi</label>
                                <select name="about_font" id="about_font" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach(['Sans-serif', 'Serif', 'Monospace', 'Poppins', 'Playfair Display', 'Roboto'] as $font)
                                        <option value="{{ $font }}" {{ old('about_font', $lp->about_font ?? 'Poppins') == $font ? 'selected' : '' }}>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="about_color" class="block text-xs font-medium dk-text mb-1">Warna Font</label>
                                <input type="color" name="about_color" id="about_color" value="{{ old('about_color', $lp->about_color ?? '#374151') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                            </div>
                            <div>
                                <label for="about_bg_color" class="block text-xs font-medium dk-text mb-1">Warna Background</label>
                                <input type="color" name="about_bg_color" id="about_bg_color" value="{{ old('about_bg_color', $lp->about_bg_color ?? '#ffffff') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Testimonial Styling --}}
                <div style="border-bottom:1px solid #1e2b3d pb-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 dk-text">Tampilan Testimonial</h3>
                    <div class="dk-grid-2 gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                        <div>
                            <label for="testimonial_title_color" class="block text-xs font-medium dk-text mb-1">Warna Judul Section</label>
                            <input type="color" name="testimonial_title_color" id="testimonial_title_color" value="{{ old('testimonial_title_color', $lp->testimonial_title_color ?? '#111827') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                        </div>
                        <div>
                            <label for="testimonial_bg_color" class="block text-xs font-medium dk-text mb-1">Warna Background Section</label>
                            <input type="color" name="testimonial_bg_color" id="testimonial_bg_color" value="{{ old('testimonial_bg_color', $lp->testimonial_bg_color ?? '#f9fafb') }}" class="w-full h-9 dk-input rounded-lg shadow-sm cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="flex items-center" style="cursor:pointer">
                        <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $lp->is_published ?? false) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#6366f1;cursor:pointer">
                        <span class="ml-2 text-sm dk-text">Publish Landing Page</span>
                    </label>
                    <p class="text-xs mt-1 dk-text-muted">Jika tidak dipublish, halaman produk default akan ditampilkan.</p>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="dk-btn dk-btn-primary">Simpan Konten Utama</button>
                <p class="text-xs mt-2 dk-text-muted">Tombol ini hanya menyimpan konten utama di atas. Galeri Gambar &amp; Testimonial punya tombol simpan sendiri di bawah.</p>
            </div>
        </form>
    </div>
</div>

{{-- Galeri Section --}}
<div class="max-w-3xl mb-8">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Galeri Gambar</h2>

        @if($product->landingPageImages->count() > 0)
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-6" id="gallery-grid">
                @foreach($product->landingPageImages as $image)
                    <div class="relative group rounded-lg overflow-hidden" style="border:1px solid #2d3a4a" data-id="{{ $image->id }}">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->caption }}" class="object-cover" style="width: 120px; height: 120px;">
                        @if($image->caption)
                            <p class="text-xs dk-text p-2 truncate">{{ $image->caption }}</p>
                        @endif
                        <form method="POST" action="{{ route('admin.products.landing-page.images.delete', [$product, $image]) }}" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Hapus gambar ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white p-1 rounded-full hover:bg-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm dk-text-muted mb-4">Belum ada gambar di galeri.</p>
        @endif

        <form method="POST" action="{{ route('admin.products.landing-page.images.upload', $product) }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="images" class="dk-label">Upload Gambar Baru</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="w-full dk-input" required>
                    <p class="text-xs mt-1 dk-text-muted">Pilih satu atau beberapa gambar. Maks 5MB per gambar.</p>
                    @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="dk-btn dk-btn-primary">Upload Gambar Sekarang</button>
                <p class="text-xs dk-text-muted">Klik tombol ini untuk menyimpan gambar ke galeri. Tidak ikut tersimpan dari tombol "Simpan Konten Utama" di atas.</p>
            </div>
        </form>
    </div>
</div>

{{-- Testimonial Section --}}
<div class="max-w-3xl mb-8">
    <div class="dk-card" style="padding:24px;">
        <h2 class="text-lg font-semibold dk-heading mb-4">Testimonial</h2>

        @if($product->landingPageTestimonials->count() > 0)
            <div class="space-y-4 mb-6">
                @foreach($product->landingPageTestimonials as $testimonial)
                    <div class="rounded-lg p-4" style="border:1px solid #2d3a4a;background:#151e2d">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                @if($testimonial->avatar)
                                    <img src="{{ asset('storage/' . $testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="rounded-full object-cover" style="width: 80px; height: 80px; max-width: 80px;">
                                @else
                                    <div class="rounded-full bg-indigo-100 flex items-center justify-center" style="width: 80px; height: 80px; min-width: 80px;">
                                        <span class="text-indigo-600 font-semibold text-sm">{{ strtoupper(substr($testimonial->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium dk-heading text-sm">{{ $testimonial->name }}</p>
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $testimonial->is_active ? 'dk-badge" style="background:rgba(16,185,129,0.15);color:#6ee7b7"' : 'dk-badge" style="background:rgba(239,68,68,0.15);color:#fca5a5"' }}">
                                    {{ $testimonial->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <form method="POST" action="{{ route('admin.products.landing-page.testimonials.toggle', [$product, $testimonial]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="dk-text-muted hover:dk-text text-xs font-medium">{{ $testimonial->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.landing-page.testimonials.delete', [$product, $testimonial]) }}" class="inline" onsubmit="return confirm('Hapus testimonial ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <p class="text-sm dk-text mt-2">{{ $testimonial->content }}</p>

                        {{-- Inline Edit Form --}}
                        <details class="mt-3">
                            <summary class="text-xs font-medium" style="color:#818cf8 cursor-pointer">Edit testimonial</summary>
                            <form method="POST" action="{{ route('admin.products.landing-page.testimonials.update', [$product, $testimonial]) }}" enctype="multipart/form-data" class="mt-3 space-y-3 dk-divider pt-3">
                                @csrf @method('PUT')
                                <div class="dk-grid-2 gap-3" style="display:grid;grid-template-columns:repeat(2,1fr)">
                                    <div>
                                        <label class="block text-xs font-medium dk-text mb-1">Nama</label>
                                        <input type="text" name="name" value="{{ $testimonial->name }}" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium dk-text mb-1">Rating</label>
                                        <select name="rating" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            @for($i = 5; $i >= 1; $i--)
                                                <option value="{{ $i }}" {{ $testimonial->rating == $i ? 'selected' : '' }}>{{ $i }} Bintang</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium dk-text mb-1">Isi Testimonial</label>
                                    <textarea name="content" rows="2" class="w-full text-sm dk-input rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>{{ $testimonial->content }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium dk-text mb-1">Foto (opsional)</label>
                                    <input type="file" name="avatar" accept="image/*" class="w-full text-sm dk-input rounded-lg shadow-sm">
                                </div>
                                <div>
                                    <label class="flex items-center" style="cursor:pointer">
                                        <input type="checkbox" name="is_active" value="1" {{ $testimonial->is_active ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#6366f1;cursor:pointer">
                                        <span class="ml-2 text-xs dk-text">Aktif</span>
                                    </label>
                                </div>
                                <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 text-xs font-medium">Update</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm dk-text-muted mb-4">Belum ada testimonial.</p>
        @endif

        {{-- Add Testimonial Form --}}
        <div class="dk-divider pt-4">
            <h3 class="text-sm font-semibold dk-heading mb-3">Tambah Testimonial Baru</h3>
            <form method="POST" action="{{ route('admin.products.landing-page.testimonials.store', $product) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div class="dk-grid-2 gap-4" style="display:grid;grid-template-columns:repeat(2,1fr)">
                        <div>
                            <label for="testi_name" class="dk-label">Nama</label>
                            <input type="text" name="name" id="testi_name" class="w-full dk-input" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="testi_rating" class="dk-label">Rating</label>
                            <select name="rating" id="testi_rating" class="w-full dk-input">
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} Bintang</option>
                                @endfor
                            </select>
                            @error('rating') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="testi_content" class="dk-label">Isi Testimonial</label>
                        <textarea name="content" id="testi_content" rows="3" class="w-full dk-input" required></textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="testi_avatar" class="dk-label">Foto (opsional)</label>
                        <input type="file" name="avatar" id="testi_avatar" accept="image/*" class="w-full dk-input">
                        @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="dk-btn dk-btn-primary">Tambah Testimonial Sekarang</button>
                    <p class="text-xs dk-text-muted">Klik tombol ini untuk menyimpan testimonial. Tidak ikut tersimpan dari tombol "Simpan Konten Utama" di atas.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7.3.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce === 'undefined') {
            return;
        }
        tinymce.init({
            selector: '#about_content',
            license_key: 'gpl',
            promotion: false,
            branding: false,
            menubar: false,
            height: 520,
            skin: 'oxide-dark',
            content_css: 'dark',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | blockquote hr | removeformat code fullscreen',
            block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Preformatted=pre; Blockquote=blockquote',
            font_size_formats: '12px 14px 16px 18px 20px 24px 28px 32px 36px 48px',
            paste_data_images: true,
            paste_as_text: false,
            extended_valid_elements: 'a[href|target|rel|title]',
            link_default_target: '_blank',
            link_assume_external_targets: 'https',
            content_style: 'body { font-family: Poppins, sans-serif; font-size: 15px; line-height: 1.6; padding: 12px; }',
            relative_urls: false,
            convert_urls: false,
        });
    });
</script>
@endsection
