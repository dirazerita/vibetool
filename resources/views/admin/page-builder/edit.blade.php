@extends('layouts.admin')
@section('title', 'Page Builder — ' . $product->title)

@section('content')
@php
    $seedTestimonials = $product->landingPageTestimonials->map(fn ($t) => [
        'name' => $t->name,
        'text' => $t->content,
        'rating' => (int) $t->rating,
    ])->values();

    $seed = [
        'productTitle' => $product->title,
        'productDescription' => (string) $product->description,
        'price' => (float) $product->price,
        'checkoutUrl' => route('checkout', $product->slug),
        'heroTitle' => $landingPage->hero_title ?? $product->title,
        'heroSubtitle' => $landingPage->hero_subtitle ?? '',
        'heroImage' => ($landingPage && $landingPage->hero_image) ? asset('storage/'.$landingPage->hero_image) : '',
        'videoUrl' => $landingPage->video_url ?? '',
        'aboutText' => $landingPage ? trim(strip_tags((string) $landingPage->about_content)) : '',
        'testimonials' => $seedTestimonials,
    ];
@endphp

<style>
    .pb-wrap { display:grid; grid-template-columns:230px 1fr 300px; gap:14px; height:calc(100vh - 130px); min-height:520px; }
    .pb-panel { overflow-y:auto; }
    .pb-palette-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid rgba(148,163,184,0.15); border-radius:10px; cursor:grab; background:rgba(148,163,184,0.04); transition:all .15s; font-size:13px; color:#cbd5e1; user-select:none; }
    .pb-palette-item:hover { border-color:#6366f1; background:rgba(99,102,241,0.1); transform:translateX(2px); }
    .pb-palette-item svg { width:17px; height:17px; color:#818cf8; flex-shrink:0; }
    .pb-canvas { background:#0d1220; border:1px solid rgba(148,163,184,0.12); border-radius:14px; overflow-y:auto; padding:20px; }
    .pb-page { margin:0 auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.5); transition:max-width .3s ease; }
    .pb-block-wrap { position:relative; }
    .pb-block-wrap:hover { outline:2px dashed rgba(99,102,241,0.5); outline-offset:-2px; }
    .pb-block-wrap.pb-selected { outline:2px solid #6366f1; outline-offset:-2px; }
    .pb-block-tools { position:absolute; top:6px; right:6px; z-index:5; display:none; gap:4px; }
    .pb-block-wrap:hover .pb-block-tools, .pb-block-wrap.pb-selected .pb-block-tools { display:flex; }
    .pb-tool-btn { width:26px; height:26px; display:flex; align-items:center; justify-content:center; background:#1e293b; color:#e2e8f0; border:1px solid #334155; border-radius:6px; cursor:pointer; padding:0; }
    .pb-tool-btn:hover { background:#4f46e5; border-color:#4f46e5; }
    .pb-tool-btn svg { width:13px; height:13px; }
    .pb-drop-line { height:4px; border-radius:2px; margin:2px 12px; background:transparent; transition:background .12s; }
    .pb-drop-line.pb-active { background:#6366f1; box-shadow:0 0 10px rgba(99,102,241,0.8); }
    .pb-field { margin-bottom:12px; }
    .pb-field label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-bottom:4px; }
    .pb-field input[type=text], .pb-field input[type=number], .pb-field input[type=url], .pb-field textarea, .pb-field select { width:100%; }
    .pb-color-row { display:flex; align-items:center; gap:8px; }
    .pb-color-row input[type=color] { width:34px; height:30px; border:1px solid #334155; border-radius:6px; background:#0f172a; padding:2px; cursor:pointer; }
    .pb-item-card { border:1px solid rgba(148,163,184,0.15); border-radius:8px; padding:10px; margin-bottom:8px; background:rgba(148,163,184,0.04); }
    .pb-toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
    .pb-view-toggle { display:flex; border:1px solid rgba(148,163,184,0.2); border-radius:10px; overflow:hidden; }
    .pb-view-toggle button { padding:7px 14px; font-size:13px; background:transparent; color:#94a3b8; border:0; cursor:pointer; display:flex; align-items:center; gap:6px; }
    .pb-view-toggle button.pb-on { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; }
    .pb-toast { position:fixed; bottom:24px; right:24px; z-index:100; padding:12px 20px; border-radius:12px; font-size:14px; font-weight:600; color:#fff; box-shadow:0 10px 30px rgba(0,0,0,0.4); }
    @media (max-width:1024px) { .pb-wrap { grid-template-columns:200px 1fr; } .pb-settings { position:fixed; right:0; top:60px; bottom:0; width:300px; z-index:60; background:#0f1729; border-left:1px solid #1e2b3d; padding:16px; } }
</style>

<div x-data="pageBuilder()" x-init="init()" @beforeunload.window="if (dirty) $event.preventDefault()">

    <div class="pb-toolbar">
        <a href="{{ route('admin.page-builder.index') }}" class="dk-btn dk-btn-outline" style="padding:7px 14px;">
            <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <div style="min-width:0;">
            <div class="dk-heading" style="font-weight:700; font-size:15px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $product->title }}</div>
            <div class="dk-text-muted" style="font-size:11px;">Page Builder — drag blok dari kiri ke kanvas</div>
        </div>
        <div style="flex:1;"></div>
        <div class="pb-view-toggle">
            <button type="button" :class="viewport === 'desktop' ? 'pb-on' : ''" @click="viewport = 'desktop'">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Desktop
            </button>
            <button type="button" :class="viewport === 'mobile' ? 'pb-on' : ''" @click="viewport = 'mobile'">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Mobile
            </button>
        </div>
        <a href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" class="dk-btn dk-btn-outline" style="padding:7px 14px;">Lihat Halaman</a>
        <button type="button" class="dk-btn dk-btn-outline" style="padding:7px 14px;" :disabled="saving" @click="save(false)">Simpan Draft</button>
        <button type="button" class="dk-btn dk-btn-primary" style="padding:7px 18px;" :disabled="saving" @click="save(true)">
            <span x-show="!saving">Simpan &amp; Publish</span>
            <span x-show="saving" x-cloak>Menyimpan...</span>
        </button>
    </div>

    <div class="pb-wrap">
        {{-- ===== PALETTE ===== --}}
        <div class="dk-card pb-panel" style="padding:14px;">
            <div class="dk-text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">Blok — tarik ke kanvas</div>
            <div style="display:flex; flex-direction:column; gap:7px;">
                <template x-for="b in palette" :key="b.type">
                    <div class="pb-palette-item" draggable="true"
                         @dragstart="dragNewType = b.type; dragIndex = null"
                         @dragend="dragNewType = null"
                         @click="addBlock(b.type)">
                        <span x-html="b.icon"></span>
                        <span x-text="b.label"></span>
                    </div>
                </template>
            </div>
            <div class="dk-divider" style="margin:14px 0;"></div>
            <div class="dk-text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">Pengaturan Halaman</div>
            <div class="pb-field">
                <label>Warna Aksen (tombol)</label>
                <div class="pb-color-row">
                    <input type="color" x-model="page.accent" @input="dirty = true">
                    <input type="text" class="dk-input" x-model="page.accent" @input="dirty = true" style="flex:1; font-size:12px;">
                </div>
            </div>
            <div class="pb-field">
                <label>Warna Latar Halaman</label>
                <div class="pb-color-row">
                    <input type="color" x-model="page.bg" @input="dirty = true">
                    <input type="text" class="dk-input" x-model="page.bg" @input="dirty = true" style="flex:1; font-size:12px;">
                </div>
            </div>
        </div>

        {{-- ===== CANVAS ===== --}}
        <div class="pb-canvas pb-panel" @dragover.prevent @drop.prevent="dropAtEnd()">
            <div x-html="'<style>' + previewCss() + '</style>'"></div>
            <div class="pb-page" :style="'max-width:' + (viewport === 'mobile' ? '375px' : '900px') + '; background:' + page.bg + ';'">
                <template x-if="blocks.length === 0">
                    <div style="padding:80px 24px; text-align:center; color:#94a3b8; border:2px dashed #cbd5e1; margin:24px; border-radius:12px;"
                         @dragover.prevent @drop.prevent="dropAtEnd()">
                        <div style="font-size:40px; margin-bottom:8px;">🎨</div>
                        <div style="font-weight:600; color:#475569;">Kanvas masih kosong</div>
                        <div style="font-size:13px;">Tarik blok dari panel kiri ke sini, atau klik blok untuk menambahkan.</div>
                    </div>
                </template>
                <template x-for="(block, i) in blocks" :key="block.id">
                    <div>
                        <div class="pb-drop-line" :class="dropTarget === i ? 'pb-active' : ''"
                             @dragover.prevent="dropTarget = i" @dragleave="dropTarget = null"
                             @drop.prevent="handleDrop(i)"></div>
                        <div class="pb-block-wrap" :class="selected === i ? 'pb-selected' : ''"
                             draggable="true"
                             @dragstart="dragIndex = i; dragNewType = null"
                             @dragend="dragIndex = null; dropTarget = null"
                             @dragover.prevent="dropTarget = i"
                             @drop.prevent="handleDrop(i)"
                             @click="selected = i">
                            <div class="pb-block-tools">
                                <button type="button" class="pb-tool-btn" title="Naik" @click.stop="moveBlock(i, -1)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg></button>
                                <button type="button" class="pb-tool-btn" title="Turun" @click.stop="moveBlock(i, 1)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                                <button type="button" class="pb-tool-btn" title="Duplikat" @click.stop="duplicateBlock(i)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg></button>
                                <button type="button" class="pb-tool-btn" title="Hapus" style="color:#f87171;" @click.stop="removeBlock(i)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>
                            <div x-html="renderBlock(block)"></div>
                        </div>
                    </div>
                </template>
                <div class="pb-drop-line" :class="dropTarget === blocks.length && blocks.length > 0 ? 'pb-active' : ''" style="height:16px;"
                     @dragover.prevent="dropTarget = blocks.length" @dragleave="dropTarget = null"
                     @drop.prevent="dropAtEnd()"></div>
            </div>
        </div>

        {{-- ===== SETTINGS ===== --}}
        <div class="dk-card pb-panel pb-settings" style="padding:14px;">
            <template x-if="selected === null || !blocks[selected]">
                <div style="text-align:center; padding:40px 10px;" class="dk-text-muted">
                    <svg style="width:34px; height:34px; margin:0 auto 10px; opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <div style="font-size:13px;">Klik salah satu blok di kanvas untuk mengedit isinya.</div>
                </div>
            </template>

            <template x-if="selected !== null && blocks[selected]">
                <div @input="dirty = true">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <span class="dk-heading" style="font-weight:700; font-size:14px;" x-text="labelFor(blocks[selected].type)"></span>
                        <button type="button" class="pb-tool-btn" title="Tutup" @click="selected = null"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>

                    {{-- HERO --}}
                    <template x-if="blocks[selected].type === 'hero'">
                        <div>
                            <div class="pb-field"><label>Judul</label><textarea class="dk-input" rows="2" x-model="blocks[selected].d.title"></textarea></div>
                            <div class="pb-field"><label>Sub Judul</label><textarea class="dk-input" rows="2" x-model="blocks[selected].d.subtitle"></textarea></div>
                            <div class="pb-field"><label>Gambar (opsional)</label>
                                <template x-if="blocks[selected].d.image"><img :src="blocks[selected].d.image" style="width:100%; border-radius:8px; margin-bottom:6px;"></template>
                                <input type="file" accept="image/*" @change="uploadImage($event, blocks[selected].d, 'image')" class="dk-input" style="font-size:12px;">
                            </div>
                            <div class="pb-field"><label>Warna Latar 1</label><div class="pb-color-row"><input type="color" x-model="blocks[selected].d.bg1"><input type="text" class="dk-input" x-model="blocks[selected].d.bg1" style="flex:1;font-size:12px;"></div></div>
                            <div class="pb-field"><label>Warna Latar 2</label><div class="pb-color-row"><input type="color" x-model="blocks[selected].d.bg2"><input type="text" class="dk-input" x-model="blocks[selected].d.bg2" style="flex:1;font-size:12px;"></div></div>
                            <div class="pb-field"><label>Teks Tombol (kosongkan utk sembunyi)</label><input type="text" class="dk-input" x-model="blocks[selected].d.btnText"></div>
                        </div>
                    </template>

                    {{-- TEXT --}}
                    <template x-if="blocks[selected].type === 'text'">
                        <div>
                            <div class="pb-field"><label>Judul Bagian (opsional)</label><input type="text" class="dk-input" x-model="blocks[selected].d.heading"></div>
                            <div class="pb-field"><label>Isi Teks</label><textarea class="dk-input" rows="6" x-model="blocks[selected].d.text"></textarea></div>
                            <div class="pb-field"><label>Perataan</label>
                                <select class="dk-input" x-model="blocks[selected].d.align"><option value="left">Kiri</option><option value="center">Tengah</option></select>
                            </div>
                            <div class="pb-field"><label>Warna Latar</label><div class="pb-color-row"><input type="color" x-model="blocks[selected].d.bg"><input type="text" class="dk-input" x-model="blocks[selected].d.bg" style="flex:1;font-size:12px;"></div></div>
                        </div>
                    </template>

                    {{-- IMAGE --}}
                    <template x-if="blocks[selected].type === 'image'">
                        <div>
                            <div class="pb-field"><label>Gambar</label>
                                <template x-if="blocks[selected].d.url"><img :src="blocks[selected].d.url" style="width:100%; border-radius:8px; margin-bottom:6px;"></template>
                                <input type="file" accept="image/*" @change="uploadImage($event, blocks[selected].d, 'url')" class="dk-input" style="font-size:12px;">
                            </div>
                            <div class="pb-field"><label>Keterangan (opsional)</label><input type="text" class="dk-input" x-model="blocks[selected].d.caption"></div>
                        </div>
                    </template>

                    {{-- VIDEO --}}
                    <template x-if="blocks[selected].type === 'video'">
                        <div>
                            <div class="pb-field"><label>URL YouTube</label><input type="url" class="dk-input" x-model="blocks[selected].d.url" placeholder="https://www.youtube.com/watch?v=..."></div>
                            <p class="dk-text-muted" style="font-size:11px;">Mendukung youtube.com/watch, youtu.be, dan /embed.</p>
                        </div>
                    </template>

                    {{-- FEATURES --}}
                    <template x-if="blocks[selected].type === 'features'">
                        <div>
                            <div class="pb-field"><label>Judul Bagian</label><input type="text" class="dk-input" x-model="blocks[selected].d.heading"></div>
                            <label style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8;">Daftar Fitur</label>
                            <template x-for="(item, k) in blocks[selected].d.items" :key="k">
                                <div class="pb-item-card">
                                    <div style="display:flex; gap:6px; margin-bottom:6px;">
                                        <input type="text" class="dk-input" x-model="item.icon" style="width:52px; text-align:center;" title="Emoji">
                                        <input type="text" class="dk-input" x-model="item.title" style="flex:1;" placeholder="Judul fitur">
                                        <button type="button" class="pb-tool-btn" style="color:#f87171;" @click="blocks[selected].d.items.splice(k,1); dirty = true;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </div>
                                    <textarea class="dk-input" rows="2" x-model="item.text" placeholder="Deskripsi singkat" style="width:100%;"></textarea>
                                </div>
                            </template>
                            <button type="button" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center; padding:6px;" @click="blocks[selected].d.items.push({icon:'✨', title:'Fitur baru', text:''}); dirty = true;">+ Tambah Fitur</button>
                        </div>
                    </template>

                    {{-- TESTIMONIALS --}}
                    <template x-if="blocks[selected].type === 'testimonials'">
                        <div>
                            <div class="pb-field"><label>Judul Bagian</label><input type="text" class="dk-input" x-model="blocks[selected].d.heading"></div>
                            <template x-for="(item, k) in blocks[selected].d.items" :key="k">
                                <div class="pb-item-card">
                                    <div style="display:flex; gap:6px; margin-bottom:6px;">
                                        <input type="text" class="dk-input" x-model="item.name" style="flex:1;" placeholder="Nama">
                                        <select class="dk-input" x-model.number="item.rating" style="width:64px;"><option>5</option><option>4</option><option>3</option></select>
                                        <button type="button" class="pb-tool-btn" style="color:#f87171;" @click="blocks[selected].d.items.splice(k,1); dirty = true;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </div>
                                    <textarea class="dk-input" rows="2" x-model="item.text" placeholder="Isi testimoni" style="width:100%;"></textarea>
                                </div>
                            </template>
                            <button type="button" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center; padding:6px;" @click="blocks[selected].d.items.push({name:'', rating:5, text:''}); dirty = true;">+ Tambah Testimoni</button>
                        </div>
                    </template>

                    {{-- FAQ --}}
                    <template x-if="blocks[selected].type === 'faq'">
                        <div>
                            <div class="pb-field"><label>Judul Bagian</label><input type="text" class="dk-input" x-model="blocks[selected].d.heading"></div>
                            <template x-for="(item, k) in blocks[selected].d.items" :key="k">
                                <div class="pb-item-card">
                                    <div style="display:flex; gap:6px; margin-bottom:6px;">
                                        <input type="text" class="dk-input" x-model="item.q" style="flex:1;" placeholder="Pertanyaan">
                                        <button type="button" class="pb-tool-btn" style="color:#f87171;" @click="blocks[selected].d.items.splice(k,1); dirty = true;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </div>
                                    <textarea class="dk-input" rows="2" x-model="item.a" placeholder="Jawaban" style="width:100%;"></textarea>
                                </div>
                            </template>
                            <button type="button" class="dk-btn dk-btn-outline" style="width:100%; justify-content:center; padding:6px;" @click="blocks[selected].d.items.push({q:'', a:''}); dirty = true;">+ Tambah FAQ</button>
                        </div>
                    </template>

                    {{-- PRICING --}}
                    <template x-if="blocks[selected].type === 'pricing'">
                        <div>
                            <div class="pb-field"><label>Judul</label><input type="text" class="dk-input" x-model="blocks[selected].d.heading"></div>
                            <div class="pb-field"><label>Harga (Rp)</label><input type="number" class="dk-input" x-model.number="blocks[selected].d.price"></div>
                            <div class="pb-field"><label>Harga Coret (opsional, Rp)</label><input type="number" class="dk-input" x-model.number="blocks[selected].d.compareAt"></div>
                            <div class="pb-field"><label>Catatan (mis. "Akses selamanya")</label><input type="text" class="dk-input" x-model="blocks[selected].d.note"></div>
                            <div class="pb-field"><label>Teks Tombol</label><input type="text" class="dk-input" x-model="blocks[selected].d.btnText"></div>
                        </div>
                    </template>

                    {{-- CTA --}}
                    <template x-if="blocks[selected].type === 'cta'">
                        <div>
                            <div class="pb-field"><label>Judul Ajakan</label><textarea class="dk-input" rows="2" x-model="blocks[selected].d.heading"></textarea></div>
                            <div class="pb-field"><label>Sub Teks</label><input type="text" class="dk-input" x-model="blocks[selected].d.sub"></div>
                            <div class="pb-field"><label>Teks Tombol</label><input type="text" class="dk-input" x-model="blocks[selected].d.btnText"></div>
                            <div class="pb-field"><label>Tujuan Tombol</label>
                                <select class="dk-input" x-model="blocks[selected].d.target">
                                    <option value="checkout">Checkout produk ini</option>
                                    <option value="custom">URL kustom</option>
                                </select>
                            </div>
                            <template x-if="blocks[selected].d.target === 'custom'">
                                <div class="pb-field"><label>URL Kustom</label><input type="url" class="dk-input" x-model="blocks[selected].d.url"></div>
                            </template>
                        </div>
                    </template>

                    {{-- SPACER --}}
                    <template x-if="blocks[selected].type === 'spacer'">
                        <div class="pb-field"><label>Tinggi (px)</label><input type="number" class="dk-input" x-model.number="blocks[selected].d.height" min="8" max="200"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Toast --}}
    <template x-if="toast">
        <div class="pb-toast" :style="'background:' + (toastOk ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#dc2626,#e11d48)')" x-text="toast"></div>
    </template>
</div>

<script>
function pageBuilder() {
    const seed = @json($seed);
    const savedJson = @json($builderJson);
    const saveUrl = @json(route('admin.page-builder.update', $product));
    const uploadUrl = @json(route('admin.page-builder.upload-image', $product));
    const csrf = document.querySelector('meta[name=csrf-token]').content;

    const esc = (s) => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const nl2br = (s) => esc(s).replace(/\n/g, '<br>');
    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    let uid = 1;

    return {
        blocks: [],
        page: { accent: '#6d28d9', bg: '#ffffff' },
        selected: null,
        viewport: 'desktop',
        dragNewType: null,
        dragIndex: null,
        dropTarget: null,
        dirty: false,
        saving: false,
        toast: '',
        toastOk: true,

        palette: [
            { type: 'hero',         label: 'Hero / Pembuka',  icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v7H4V5zm0 9h16v5a1 1 0 01-1 1H5a1 1 0 01-1-1v-5z"/></svg>' },
            { type: 'text',         label: 'Teks / Deskripsi', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>' },
            { type: 'image',        label: 'Gambar',          icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
            { type: 'video',        label: 'Video YouTube',   icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>' },
            { type: 'features',     label: 'Fitur / Benefit', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>' },
            { type: 'testimonials', label: 'Testimoni',       icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>' },
            { type: 'pricing',      label: 'Harga / Penawaran', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { type: 'faq',          label: 'FAQ',             icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { type: 'cta',          label: 'Ajakan Beli (CTA)', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>' },
            { type: 'spacer',       label: 'Spasi',           icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>' },
        ],

        labelFor(type) { const p = this.palette.find(x => x.type === type); return p ? p.label : type; },

        defaults(type) {
            switch (type) {
                case 'hero': return { title: seed.heroTitle, subtitle: seed.heroSubtitle || seed.productDescription.slice(0, 140), image: seed.heroImage, bg1: '#1e1b4b', bg2: '#4c1d95', btnText: 'Beli Sekarang' };
                case 'text': return { heading: 'Tentang Produk', text: seed.aboutText || seed.productDescription, align: 'left', bg: '#ffffff' };
                case 'image': return { url: '', caption: '' };
                case 'video': return { url: seed.videoUrl || '' };
                case 'features': return { heading: 'Apa yang Kamu Dapatkan', items: [
                    { icon: '🚀', title: 'Langsung Bisa Dipakai', text: 'Akses instan setelah pembayaran dikonfirmasi.' },
                    { icon: '💎', title: 'Kualitas Premium', text: 'Dibuat serius untuk hasil maksimal.' },
                    { icon: '🛟', title: 'Support Ramah', text: 'Tim kami siap bantu kalau ada kendala.' },
                ] };
                case 'testimonials': return { heading: 'Kata Mereka', items: seed.testimonials.length ? seed.testimonials : [{ name: 'Pelanggan Puas', rating: 5, text: 'Produknya sangat membantu!' }] };
                case 'pricing': return { heading: 'Penawaran Spesial', price: seed.price, compareAt: 0, note: 'Sekali bayar — akses selamanya', btnText: 'Ambil Sekarang' };
                case 'faq': return { heading: 'Pertanyaan Umum', items: [
                    { q: 'Bagaimana cara mengakses produk setelah membeli?', a: 'Setelah pembayaran dikonfirmasi, produk bisa langsung diakses dari dashboard akunmu.' },
                    { q: 'Apakah ada garansi?', a: 'Hubungi admin kalau ada kendala — kami siap membantu.' },
                ] };
                case 'cta': return { heading: 'Siap Mulai Sekarang?', sub: 'Jangan tunda lagi — mulai hari ini.', btnText: 'Beli Sekarang', target: 'checkout', url: '' };
                case 'spacer': return { height: 40 };
            }
            return {};
        },

        init() {
            if (savedJson) {
                try {
                    const parsed = JSON.parse(savedJson);
                    this.blocks = (parsed.blocks || []).map(b => ({ id: uid++, type: b.type, d: b.d }));
                    if (parsed.page) this.page = Object.assign(this.page, parsed.page);
                    return;
                } catch (e) { /* fallthrough ke template default */ }
            }
            // Template awal: susunan landing page standar dari data produk yang ada.
            ['hero', 'features', 'text', 'video', 'testimonials', 'pricing', 'faq', 'cta'].forEach(t => {
                if (t === 'video' && !seed.videoUrl) return;
                this.blocks.push({ id: uid++, type: t, d: this.defaults(t) });
            });
        },

        addBlock(type) {
            this.blocks.push({ id: uid++, type, d: this.defaults(type) });
            this.selected = this.blocks.length - 1;
            this.dirty = true;
        },
        removeBlock(i) {
            this.blocks.splice(i, 1);
            this.selected = null;
            this.dirty = true;
        },
        duplicateBlock(i) {
            this.blocks.splice(i + 1, 0, { id: uid++, type: this.blocks[i].type, d: JSON.parse(JSON.stringify(this.blocks[i].d)) });
            this.dirty = true;
        },
        moveBlock(i, dir) {
            const j = i + dir;
            if (j < 0 || j >= this.blocks.length) return;
            const [b] = this.blocks.splice(i, 1);
            this.blocks.splice(j, 0, b);
            this.selected = j;
            this.dirty = true;
        },
        handleDrop(i) {
            if (this.dragNewType) {
                this.blocks.splice(i, 0, { id: uid++, type: this.dragNewType, d: this.defaults(this.dragNewType) });
                this.selected = i;
            } else if (this.dragIndex !== null && this.dragIndex !== i) {
                const from = this.dragIndex;
                const [b] = this.blocks.splice(from, 1);
                const to = from < i ? i - 1 : i;
                this.blocks.splice(to, 0, b);
                this.selected = to;
            }
            this.dragNewType = null; this.dragIndex = null; this.dropTarget = null;
            this.dirty = true;
        },
        dropAtEnd() { this.handleDrop(this.blocks.length); },

        youtubeEmbed(url) {
            const m = String(url || '').match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{6,})/);
            return m ? 'https://www.youtube.com/embed/' + m[1] : null;
        },
        ctaHref(d) { return d.target === 'custom' && d.url ? d.url : seed.checkoutUrl; },

        /* ---------- Render blok → HTML (dipakai preview & hasil akhir) ---------- */
        renderBlock(b) {
            const d = b.d, ac = this.page.accent;
            const btn = (text, href) => '<a href="' + esc(href) + '" class="pb-btn" style="background:' + esc(ac) + ';">' + esc(text) + '</a>';
            switch (b.type) {
                case 'hero':
                    return '<div class="pb-hero" style="background:linear-gradient(135deg,' + esc(d.bg1) + ',' + esc(d.bg2) + ');">'
                        + (d.image ? '<img src="' + esc(d.image) + '" alt="" class="pb-hero-img">' : '')
                        + '<h1>' + nl2br(d.title) + '</h1>'
                        + (d.subtitle ? '<p>' + nl2br(d.subtitle) + '</p>' : '')
                        + (d.btnText ? btn(d.btnText, seed.checkoutUrl) : '')
                        + '</div>';
                case 'text':
                    return '<div class="pb-sec" style="background:' + esc(d.bg) + '; text-align:' + (d.align === 'center' ? 'center' : 'left') + ';">'
                        + (d.heading ? '<h2>' + esc(d.heading) + '</h2>' : '')
                        + '<div class="pb-body">' + nl2br(d.text) + '</div></div>';
                case 'image':
                    if (!d.url) return '<div class="pb-sec" style="text-align:center; color:#94a3b8; border:2px dashed #cbd5e1; margin:12px; border-radius:10px;">Klik blok ini lalu upload gambar di panel kanan</div>';
                    return '<div class="pb-sec" style="text-align:center;"><img src="' + esc(d.url) + '" alt="' + esc(d.caption) + '" class="pb-img">'
                        + (d.caption ? '<div class="pb-caption">' + esc(d.caption) + '</div>' : '') + '</div>';
                case 'video': {
                    const emb = this.youtubeEmbed(d.url);
                    if (!emb) return '<div class="pb-sec" style="text-align:center; color:#94a3b8; border:2px dashed #cbd5e1; margin:12px; border-radius:10px;">Isi URL YouTube di panel kanan</div>';
                    return '<div class="pb-sec"><div class="pb-video"><iframe src="' + esc(emb) + '" title="Video" allowfullscreen loading="lazy"></iframe></div></div>';
                }
                case 'features':
                    return '<div class="pb-sec">'
                        + (d.heading ? '<h2 style="text-align:center;">' + esc(d.heading) + '</h2>' : '')
                        + '<div class="pb-feat-grid">'
                        + d.items.map(it => '<div class="pb-feat"><div class="pb-feat-icon">' + esc(it.icon) + '</div><div class="pb-feat-title">' + esc(it.title) + '</div><div class="pb-feat-text">' + nl2br(it.text) + '</div></div>').join('')
                        + '</div></div>';
                case 'testimonials':
                    return '<div class="pb-sec" style="background:#f8fafc;">'
                        + (d.heading ? '<h2 style="text-align:center;">' + esc(d.heading) + '</h2>' : '')
                        + '<div class="pb-testi-grid">'
                        + d.items.map(it => '<div class="pb-testi"><div class="pb-stars">' + '★'.repeat(Math.max(1, Math.min(5, it.rating || 5))) + '</div><p>&ldquo;' + nl2br(it.text) + '&rdquo;</p><div class="pb-testi-name">' + esc(it.name) + '</div></div>').join('')
                        + '</div></div>';
                case 'pricing': {
                    const compare = d.compareAt && d.compareAt > d.price ? '<div class="pb-compare">' + rupiah(d.compareAt) + '</div>' : '';
                    return '<div class="pb-sec" style="text-align:center;">'
                        + '<div class="pb-price-card" style="border-color:' + esc(ac) + ';">'
                        + (d.heading ? '<h2>' + esc(d.heading) + '</h2>' : '')
                        + compare
                        + '<div class="pb-price" style="color:' + esc(ac) + ';">' + rupiah(d.price) + '</div>'
                        + (d.note ? '<div class="pb-note">' + esc(d.note) + '</div>' : '')
                        + (d.btnText ? btn(d.btnText, seed.checkoutUrl) : '')
                        + '</div></div>';
                }
                case 'faq':
                    return '<div class="pb-sec">'
                        + (d.heading ? '<h2 style="text-align:center;">' + esc(d.heading) + '</h2>' : '')
                        + d.items.map(it => '<details class="pb-faq"><summary>' + esc(it.q) + '</summary><div class="pb-faq-a">' + nl2br(it.a) + '</div></details>').join('')
                        + '</div>';
                case 'cta':
                    return '<div class="pb-cta" style="background:linear-gradient(135deg,' + esc(ac) + ',#312e81);">'
                        + '<h2>' + nl2br(d.heading) + '</h2>'
                        + (d.sub ? '<p>' + esc(d.sub) + '</p>' : '')
                        + '<a href="' + esc(this.ctaHref(d)) + '" class="pb-btn pb-btn-light">' + esc(d.btnText) + '</a>'
                        + '</div>';
                case 'spacer':
                    return '<div style="height:' + Math.max(8, Math.min(200, d.height || 40)) + 'px;"></div>';
            }
            return '';
        },

        /* CSS bersama preview & halaman final — mobile-friendly by default */
        previewCss() {
            return `
.pb-page, .pb-final { font-family:'Poppins','Figtree',system-ui,sans-serif; color:#1f2937; line-height:1.65; }
.pb-page *, .pb-final * { box-sizing:border-box; }
.pb-page img, .pb-final img { max-width:100%; height:auto; }
.pb-hero { padding:clamp(40px,8vw,80px) 24px; text-align:center; color:#fff; }
.pb-hero h1 { font-size:clamp(1.7rem,5vw,2.8rem); font-weight:800; line-height:1.2; margin:0 0 14px; }
.pb-hero p { font-size:clamp(1rem,2.5vw,1.2rem); opacity:.9; margin:0 auto 26px; max-width:640px; }
.pb-hero-img { max-width:min(420px,80%); border-radius:14px; margin-bottom:22px; box-shadow:0 16px 40px rgba(0,0,0,.35); }
.pb-btn { display:inline-block; color:#fff; padding:14px 36px; border-radius:12px; font-weight:700; font-size:1.05rem; text-decoration:none; box-shadow:0 8px 24px rgba(0,0,0,.25); }
.pb-btn-light { background:#fff !important; color:#1f2937; }
.pb-sec { padding:clamp(28px,5vw,52px) 24px; max-width:820px; margin:0 auto; }
.pb-sec h2 { font-size:clamp(1.3rem,3.5vw,1.8rem); font-weight:800; margin:0 0 18px; color:#111827; }
.pb-body { font-size:1rem; color:#374151; white-space:normal; }
.pb-img { border-radius:12px; }
.pb-caption { font-size:.85rem; color:#6b7280; margin-top:8px; }
.pb-video { position:relative; padding-bottom:56.25%; height:0; border-radius:12px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.15); }
.pb-video iframe { position:absolute; inset:0; width:100%; height:100%; border:0; }
.pb-feat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-top:8px; }
.pb-feat { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:22px 18px; text-align:center; }
.pb-feat-icon { font-size:2rem; margin-bottom:10px; }
.pb-feat-title { font-weight:700; color:#111827; margin-bottom:6px; }
.pb-feat-text { font-size:.9rem; color:#4b5563; }
.pb-testi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; margin-top:8px; }
.pb-testi { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; }
.pb-stars { color:#f59e0b; letter-spacing:2px; margin-bottom:8px; }
.pb-testi p { font-size:.92rem; color:#374151; margin:0 0 10px; font-style:italic; }
.pb-testi-name { font-weight:700; font-size:.85rem; color:#111827; }
.pb-price-card { display:inline-block; border:2px solid; border-radius:18px; padding:30px clamp(24px,6vw,56px); background:#fff; box-shadow:0 14px 40px rgba(0,0,0,.1); max-width:100%; }
.pb-compare { color:#9ca3af; text-decoration:line-through; font-size:1.05rem; }
.pb-price { font-size:clamp(1.9rem,6vw,2.6rem); font-weight:800; margin:2px 0 6px; }
.pb-note { color:#6b7280; font-size:.9rem; margin-bottom:18px; }
.pb-faq { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:10px; padding:0; }
.pb-faq summary { cursor:pointer; font-weight:600; color:#111827; padding:15px 18px; list-style-position:outside; }
.pb-faq-a { padding:0 18px 15px; color:#4b5563; font-size:.93rem; }
.pb-cta { padding:clamp(40px,8vw,72px) 24px; text-align:center; color:#fff; }
.pb-cta h2 { font-size:clamp(1.4rem,4vw,2rem); font-weight:800; margin:0 0 10px; }
.pb-cta p { opacity:.9; margin:0 0 24px; }
@media (max-width:640px) {
    .pb-btn { display:block; width:100%; max-width:340px; margin-left:auto; margin-right:auto; }
    .pb-price-card { display:block; }
}`;
        },

        compileHtml() {
            const body = this.blocks.map(b => this.renderBlock(b)).join('\n');
            return '<!DOCTYPE html>\n<html lang="id">\n<head>\n'
                + '<meta charset="utf-8">\n<meta name="viewport" content="width=device-width, initial-scale=1">\n'
                + '<title>' + esc(seed.productTitle) + '</title>\n'
                + '<link rel="preconnect" href="https://fonts.bunny.net">\n'
                + '<link href="https://fonts.bunny.net/css?family=poppins:400,600,700,800&display=swap" rel="stylesheet">\n'
                + '<style>body{margin:0;background:' + esc(this.page.bg) + ';}' + this.previewCss() + '</style>\n'
                + '</head>\n<body>\n<div class="pb-final">\n' + body + '\n</div>\n</body>\n</html>';
        },

        async uploadImage(event, target, key) {
            const file = event.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await window.axios.post(uploadUrl, fd, { headers: { 'X-CSRF-TOKEN': csrf } });
                target[key] = res.data.url;
                this.dirty = true;
            } catch (e) {
                this.showToast('Upload gagal — pastikan file gambar maks 5MB.', false);
            }
            event.target.value = '';
        },

        async save(publish) {
            if (this.blocks.length === 0) { this.showToast('Kanvas masih kosong — tambahkan blok dulu.', false); return; }
            this.saving = true;
            try {
                const res = await window.axios.put(saveUrl, {
                    builder_json: JSON.stringify({ page: this.page, blocks: this.blocks.map(b => ({ type: b.type, d: b.d })) }),
                    full_html: this.compileHtml(),
                    publish: publish,
                }, { headers: { 'X-CSRF-TOKEN': csrf } });
                this.dirty = false;
                this.showToast(res.data.message, true);
            } catch (e) {
                this.showToast('Gagal menyimpan. Coba lagi.', false);
            }
            this.saving = false;
        },

        showToast(msg, ok) {
            this.toast = msg; this.toastOk = ok;
            setTimeout(() => { this.toast = ''; }, 3000);
        },
    };
}
</script>
@endsection
