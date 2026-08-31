@extends('layouts.admin')
@section('title', 'Landing Page AI — ' . $product->title)

@section('content')
<h1 class="text-2xl font-bold dk-heading mb-6">{{ $product->title }}</h1>

<div class="mb-6 " style="border-bottom:1px solid #1e2b3d">
    <nav class="flex space-x-8">
        <a href="{{ route('admin.products.edit', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Produk</a>
        <a href="{{ route('admin.products.landing-page', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Landing Page</a>
        <a href="{{ route('admin.products.landing-page-ai', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid #6366f1;color:#a5b4fc">Landing Page AI</a>
        <a href="{{ route('admin.products.video-tutorials', $product) }}" class="px-1 pb-3 text-sm font-medium" style="border-bottom:2px solid transparent;color:#64748b">Video Tutorial</a>
    </nav>
</div>

<style>[x-cloak]{display:none!important}</style>
<div x-data="lpAi()">
    <div class="dk-card mb-6" style="padding:20px;">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold dk-heading flex items-center gap-2">🤖 Landing Page hasil AI</h2>
                @if($landingPage && $landingPage->ai_generated_at)
                    <p class="text-xs dk-text-muted mt-1">
                        Terakhir digenerate: {{ \Illuminate\Support\Carbon::parse($landingPage->ai_generated_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                        @if($landingPage->use_full_html && $landingPage->is_published && $landingPage->full_html === $landingPage->ai_html)
                            · <span style="color:#34d399;">Sedang LIVE</span>
                        @elseif($landingPage->ai_html)
                            · <span style="color:#f59e0b;">Belum diterapkan / sudah diubah manual</span>
                        @endif
                    </p>
                @else
                    <p class="text-xs dk-text-muted mt-1">Belum ada hasil AI untuk produk ini.</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="regenerate()" :disabled="busy || !falReady"
                        class="px-4 py-2 rounded-lg text-sm font-medium"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;"
                        x-text="busy ? '⏳ Sedang membuat…' : '✨ Generate Ulang'"></button>
                @if($landingPage && $landingPage->ai_html)
                    <button type="button" @click="apply()" :disabled="busy"
                            class="px-4 py-2 rounded-lg text-sm font-medium"
                            style="border:1px solid #34d399; color:#34d399;">Terapkan sebagai LP Live</button>
                @endif
                <a href="{{ route('product.show', $product->slug) }}" target="_blank"
                   class="px-4 py-2 rounded-lg text-sm font-medium" style="border:1px solid #334155; color:#cbd5e1;">Lihat Live ↗</a>
            </div>
        </div>
        @unless($falReady)
            <p class="text-sm mt-3 p-3 rounded-lg" style="background:rgba(245,158,11,.1); color:#f59e0b;">
                FAL.AI API Key belum diisi — isi dulu di <a href="{{ route('admin.settings') }}" style="text-decoration:underline;">Admin → Settings → AI Agent (FAL.AI)</a>.
            </p>
        @endunless
        <p x-show="message" x-cloak class="text-sm mt-3 p-3 rounded-lg" :style="isError ? 'background:rgba(248,113,113,.1); color:#f87171;' : 'background:rgba(52,211,153,.1); color:#34d399;'" x-text="message"></p>
    </div>

    @if($landingPage && $landingPage->ai_html)
        <div class="dk-card" style="padding:12px;">
            <p class="text-xs dk-text-muted mb-2 px-2">Preview (desktop):</p>
            <iframe sandbox="" srcdoc="{{ $landingPage->ai_html }}"
                    style="width:100%; height:75vh; border:1px solid #1e2b3d; border-radius:12px; background:#fff;"></iframe>
        </div>
    @endif
</div>

<script>
function lpAi() {
    return {
        busy: false,
        message: null,
        isError: false,
        falReady: @json($falReady),
        async call(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body || {}),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) throw new Error(data.message || ('Gagal (HTTP ' + res.status + ')'));
            return data;
        },
        async regenerate() {
            this.busy = true; this.message = 'AI sedang menyusun landing page baru — ini bisa 1-2 menit…'; this.isError = false;
            try {
                await this.call('{{ route('admin.ai-agent.landing-page', $product) }}');
                this.message = 'Landing page baru selesai! Memuat ulang…';
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                this.isError = true; this.message = e.message; this.busy = false;
            }
        },
        async apply() {
            this.busy = true; this.message = null; this.isError = false;
            try {
                await this.call('{{ route('admin.products.landing-page-ai.apply', $product) }}');
                this.message = 'Diterapkan! Landing page AI sekarang LIVE.';
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                this.isError = true; this.message = e.message; this.busy = false;
            }
        },
    };
}
</script>
@endsection
