@extends('layouts.dashboard')
@section('title', 'Promo & Share')

@section('content')
<style>
    .promo-tabs { display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap; }
    .promo-tab { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:9999px; background:#151e2d; border:1px solid #2d3a4a; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500; }
    .promo-tab.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .promo-tab .count { background:rgba(255,255,255,0.18); padding:1px 8px; border-radius:9999px; font-size:11px; font-weight:600; }
    .promo-card { background:#151e2d; border:1px solid #2d3a4a; border-radius:14px; padding:18px; margin-bottom:14px; }
    .promo-card .title { font-size:16px; font-weight:600; color:#e2e8f0; }
    .promo-card .product-tag { font-size:12px; color:#a5b4fc; margin-top:3px; }
    .promo-body { background:#0f1729; border:1px solid #1e2b3d; border-radius:10px; padding:14px; margin-top:12px; color:#e2e8f0; font-size:14px; line-height:1.6; white-space:pre-wrap; word-break:break-word; max-height:280px; overflow-y:auto; }
    .promo-actions { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }
    .promo-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:9px; background:#1e2b3d; border:1px solid #2d3a4a; color:#cbd5e1; cursor:pointer; font-size:13px; font-weight:500; text-decoration:none; transition: background 0.15s, border-color 0.15s; }
    .promo-btn:hover { background:#2d3a4a; border-color:#475569; }
    .promo-btn.primary { background:#4f46e5; border-color:#4f46e5; color:#fff; }
    .promo-btn.primary:hover { background:#4338ca; }
    .promo-btn.copied { background:#16a34a; border-color:#16a34a; color:#fff; }
    .promo-btn svg { width:16px; height:16px; }

    .promo-empty { text-align:center; padding:48px 16px; color:#94a3b8; background:#151e2d; border:1px dashed #2d3a4a; border-radius:14px; font-size:14px; }
    .promo-help { background:rgba(79,70,229,0.08); border:1px solid rgba(79,70,229,0.25); border-radius:12px; padding:14px 16px; margin-bottom:18px; color:#cbd5e1; font-size:13px; line-height:1.6; }

    /* Share modal */
    .share-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.7); display:none; align-items:center; justify-content:center; z-index:1000; padding:20px; }
    .share-overlay.open { display:flex; }
    .share-modal { background:#0f1729; border:1px solid #2d3a4a; border-radius:16px; padding:24px; max-width:420px; width:100%; }
    .share-modal h3 { font-size:18px; font-weight:600; color:#e2e8f0; margin-bottom:14px; }
    .share-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; }
    .share-opt { display:flex; flex-direction:column; align-items:center; gap:6px; padding:14px 8px; border-radius:12px; background:#1e2b3d; border:1px solid #2d3a4a; color:#e2e8f0; cursor:pointer; text-decoration:none; font-size:12px; font-weight:500; transition: background 0.15s, border-color 0.15s; }
    .share-opt:hover { background:#2d3a4a; border-color:#475569; }
    .share-opt svg { width:28px; height:28px; }
    .share-opt.wa svg { color:#25D366; }
    .share-opt.tg svg { color:#229ED9; }
    .share-opt.fb svg { color:#1877F2; }
    .share-opt.tw svg { color:#1DA1F2; }
    .share-opt.email svg { color:#94a3b8; }
    .share-opt.copy svg { color:#a5b4fc; }
    .share-close { margin-top:14px; width:100%; padding:10px; background:#1e2b3d; border:1px solid #2d3a4a; border-radius:8px; color:#cbd5e1; cursor:pointer; font-size:13px; }
    .share-close:hover { background:#2d3a4a; }
</style>

<div style="margin-bottom:18px;">
    <h1 class="text-2xl font-bold dk-heading">Promo & Share</h1>
    <p class="dk-text-muted" style="font-size:14px; margin-top:4px;">Template promo siap pakai. Klik <strong>Salin</strong> untuk copy ke clipboard, atau <strong>Bagikan</strong> untuk langsung post ke WhatsApp / Telegram / Facebook / dll.</p>
</div>

<div class="promo-help">
    <strong>Cara pakai:</strong> Pilih template di bawah → klik <strong>Bagikan</strong> → pilih platform. Link affiliate Anda (<code style="color:#a5b4fc;">?ref={{ auth()->user()->referral_code }}</code>) sudah otomatis disisipkan, jadi siapa pun yang daftar dari link ini akan tercatat sebagai downline Anda.
</div>

<div class="promo-tabs">
    <a href="{{ route('dashboard.promo.index', ['category' => 'member']) }}" class="promo-tab {{ $category === 'member' ? 'active' : '' }}">Promo Member <span class="count">{{ $counts['member'] }}</span></a>
    <a href="{{ route('dashboard.promo.index', ['category' => 'product']) }}" class="promo-tab {{ $category === 'product' ? 'active' : '' }}">Promo Produk <span class="count">{{ $counts['product'] }}</span></a>
</div>

@forelse($templates as $t)
    <div class="promo-card" data-id="{{ $t['id'] }}">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div style="flex:1; min-width:0;">
                <div class="title">{{ $t['title'] }}</div>
                @if($t['category'] === 'product' && $t['product'])
                    <div class="product-tag">Untuk produk: {{ $t['product']->title }}</div>
                @endif
            </div>
        </div>
        <div class="promo-body" id="promo-body-{{ $t['id'] }}">{{ $t['body'] }}</div>
        <div class="promo-actions">
            <button type="button" class="promo-btn primary copy-btn" data-target="promo-body-{{ $t['id'] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h4a2 2 0 002-2M8 5a2 2 0 012-2h4a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                Salin Teks
            </button>
            <button type="button" class="promo-btn share-btn" data-target="promo-body-{{ $t['id'] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                Bagikan
            </button>
        </div>
    </div>
@empty
    <div class="promo-empty">
        Belum ada template promo untuk kategori ini. Tunggu admin menambahkan, atau coba kategori lain.
    </div>
@endforelse

{{-- Share modal --}}
<div class="share-overlay" id="shareModal" @click.self="document.getElementById('shareModal').classList.remove('open')">
    <div class="share-modal">
        <h3>Bagikan ke</h3>
        <div class="share-grid">
            <a href="#" target="_blank" rel="noopener" class="share-opt wa" data-platform="wa">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.768.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <a href="#" target="_blank" rel="noopener" class="share-opt tg" data-platform="tg">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Telegram
            </a>
            <a href="#" target="_blank" rel="noopener" class="share-opt fb" data-platform="fb">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
            </a>
            <a href="#" target="_blank" rel="noopener" class="share-opt tw" data-platform="tw">
                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                Twitter / X
            </a>
            <a href="#" class="share-opt email" data-platform="email">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email
            </a>
            <button type="button" class="share-opt copy" data-platform="copy">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h4a2 2 0 002-2M8 5a2 2 0 012-2h4a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                Salin Saja
            </button>
        </div>
        <button type="button" class="share-close" onclick="document.getElementById('shareModal').classList.remove('open')">Tutup</button>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('shareModal');
    let activeText = '';

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        // fallback for http
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return Promise.resolve();
    }

    function flashCopied(btn) {
        const originalHTML = btn.innerHTML;
        const originalClass = btn.className;
        btn.className = originalClass + ' copied';
        btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Tersalin!';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.className = originalClass;
        }, 1800);
    }

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            copyText(target.innerText).then(() => flashCopied(btn));
        });
    });

    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.target);
            if (!target) return;
            activeText = target.innerText;
            // Update share URLs
            const enc = encodeURIComponent(activeText);
            modal.querySelector('[data-platform=wa]').href = 'https://wa.me/?text=' + enc;
            modal.querySelector('[data-platform=tg]').href = 'https://t.me/share/url?url=&text=' + enc;
            // Facebook sharer.php only accepts URL — extract first http(s) link from text, fallback to ?u=root
            const urlMatch = activeText.match(/https?:\/\/[^\s]+/);
            const fbUrl = urlMatch ? urlMatch[0] : @json(url('/'));
            modal.querySelector('[data-platform=fb]').href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(fbUrl) + '&quote=' + enc;
            modal.querySelector('[data-platform=tw]').href = 'https://twitter.com/intent/tweet?text=' + enc;
            modal.querySelector('[data-platform=email]').href = 'mailto:?subject=' + encodeURIComponent(@json(config('app.name')) + ' — Info Menarik') + '&body=' + enc;
            modal.classList.add('open');
        });
    });

    modal.querySelector('[data-platform=copy]').addEventListener('click', (e) => {
        e.preventDefault();
        copyText(activeText).then(() => {
            const btn = e.currentTarget;
            const original = btn.innerHTML;
            btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Tersalin!';
            setTimeout(() => { btn.innerHTML = original; modal.classList.remove('open'); }, 1100);
        });
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') modal.classList.remove('open');
    });
})();
</script>
@endsection
