{{--
    DK THEME — Dark Premium Glassmorphism
    Satu sumber gaya untuk layout dashboard member & admin.
    PENTING: semua nama class & breakpoint responsive dipertahankan persis
    dari tema lama — hanya visualnya yang di-upgrade. Jangan mengubah
    struktur (width sidebar, padding shell, perilaku grid) tanpa mengecek
    kedua layout.
    Rollback: scripts/restore-old-ui.sh (tag ui-v1-original).
--}}
<style>
    :root {
        --dk-bg: #070b17;
        --dk-surface: rgba(23, 30, 56, 0.55);
        --dk-surface-solid: #161e33;
        --dk-surface-deep: rgba(13, 18, 36, 0.6);
        --dk-border: rgba(129, 140, 248, 0.14);
        --dk-border-soft: rgba(148, 163, 184, 0.1);
        --dk-primary: #6366f1;
        --dk-violet: #8b5cf6;
        --dk-glow: rgba(99, 102, 241, 0.35);
    }

    body {
        background: var(--dk-bg) !important;
        color: #e2e8f0 !important;
        min-height: 100vh;
    }

    /* Aurora ambient background — radial-gradient murni (murah utk GPU mobile) */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(ellipse 60% 45% at 12% -5%, rgba(99, 102, 241, 0.16), transparent 60%),
            radial-gradient(ellipse 50% 40% at 95% 10%, rgba(139, 92, 246, 0.12), transparent 60%),
            radial-gradient(ellipse 55% 45% at 50% 110%, rgba(56, 189, 248, 0.07), transparent 60%),
            var(--dk-bg);
    }

    /* Scrollbar halus */
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.25); border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.45); }

    /* ---------- Sidebar ---------- */
    .dk-sidebar-link { display:flex; align-items:center; padding:10px 16px; font-size:14px; font-weight:500; border-radius:12px; color:#94a3b8; transition:all 0.2s ease; text-decoration:none; position:relative; }
    .dk-sidebar-link:hover { background:rgba(99,102,241,0.12); color:#c7d2fe; transform:translateX(2px); }
    .dk-sidebar-link.active { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 4px 18px rgba(99,102,241,0.4), inset 0 1px 0 rgba(255,255,255,0.15); }
    .dk-sidebar-link svg { width:20px; height:20px; margin-right:12px; flex-shrink:0; }

    /* ---------- Surfaces (glass) ---------- */
    .dk-card {
        background: var(--dk-surface-solid);
        background: linear-gradient(160deg, rgba(30, 38, 66, 0.72), rgba(17, 23, 43, 0.66));
        -webkit-backdrop-filter: blur(14px);
        backdrop-filter: blur(14px);
        border: 1px solid var(--dk-border);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(2, 6, 23, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
    }
    .dk-card:hover { border-color: rgba(129, 140, 248, 0.28); box-shadow: 0 12px 40px rgba(2, 6, 23, 0.45), 0 0 24px rgba(99, 102, 241, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.06); }

    .dk-table {
        background: var(--dk-surface-solid);
        background: linear-gradient(160deg, rgba(30, 38, 66, 0.72), rgba(17, 23, 43, 0.66));
        -webkit-backdrop-filter: blur(14px);
        backdrop-filter: blur(14px);
        border: 1px solid var(--dk-border);
        border-radius: 16px;
        overflow-x: auto;
        box-shadow: 0 8px 32px rgba(2, 6, 23, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }
    .dk-table thead { background: rgba(10, 14, 28, 0.55); }
    .dk-table th { color:#94a3b8 !important; border-bottom:1px solid var(--dk-border); padding:12px 24px; text-align:left; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
    .dk-table td { color:#cbd5e1 !important; border-bottom:1px solid rgba(148,163,184,0.07); padding:16px 24px; font-size:14px; }
    .dk-table tbody tr { transition: background 0.15s ease; }
    .dk-table tbody tr:hover { background: rgba(99, 102, 241, 0.07); }
    .dk-table tbody tr:last-child td { border-bottom:none; }

    /* ---------- Form ---------- */
    .dk-input { background:rgba(9,13,26,0.65) !important; color:#e2e8f0 !important; border:1px solid rgba(148,163,184,0.16) !important; border-radius:10px; padding:8px 12px; font-size:14px; transition:border-color 0.2s ease, box-shadow 0.2s ease; }
    .dk-input:focus { border-color:#6366f1 !important; outline:none; box-shadow:0 0 0 3px rgba(99,102,241,0.18), 0 0 20px rgba(99,102,241,0.08); }
    .dk-input::placeholder { color:#64748b !important; }
    select.dk-input { appearance:auto; }
    textarea.dk-input { resize:vertical; }

    .dk-checkbox { appearance:none; -webkit-appearance:none; width:18px; height:18px; background:rgba(9,13,26,0.65); border:2px solid #475569; border-radius:5px; cursor:pointer; position:relative; transition:all 0.15s; flex-shrink:0; margin-top:2px; }
    .dk-checkbox:hover { border-color:#6366f1; }
    .dk-checkbox:checked { background:linear-gradient(135deg,#4f46e5,#7c3aed); border-color:#4f46e5; }
    .dk-checkbox:checked::after { content:''; position:absolute; left:4px; top:0; width:6px; height:11px; border:solid #fff; border-width:0 2px 2px 0; transform:rotate(45deg); }
    .dk-checkbox:focus-visible { outline:none; box-shadow:0 0 0 3px rgba(99,102,241,0.25); }

    /* ---------- Buttons ---------- */
    .dk-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 20px; border-radius:12px; font-weight:600; font-size:14px; border:none; cursor:pointer; transition:all 0.2s ease; text-decoration:none; }
    .dk-btn:active { transform:scale(0.98); }
    .dk-btn-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 2px 12px rgba(99,102,241,0.3), inset 0 1px 0 rgba(255,255,255,0.18); }
    .dk-btn-primary:hover { box-shadow:0 6px 22px rgba(99,102,241,0.5), inset 0 1px 0 rgba(255,255,255,0.18); transform:translateY(-1px); color:#fff; filter:brightness(1.08); }
    .dk-btn-success { background:linear-gradient(135deg,#059669,#10b981); color:#fff; box-shadow:0 2px 12px rgba(16,185,129,0.3), inset 0 1px 0 rgba(255,255,255,0.18); }
    .dk-btn-success:hover { box-shadow:0 6px 22px rgba(16,185,129,0.5), inset 0 1px 0 rgba(255,255,255,0.18); transform:translateY(-1px); color:#fff; filter:brightness(1.08); }
    .dk-btn-danger { background:linear-gradient(135deg,#dc2626,#e11d48); color:#fff; box-shadow:0 2px 12px rgba(239,68,68,0.3), inset 0 1px 0 rgba(255,255,255,0.18); }
    .dk-btn-danger:hover { box-shadow:0 6px 22px rgba(239,68,68,0.5), inset 0 1px 0 rgba(255,255,255,0.18); transform:translateY(-1px); color:#fff; filter:brightness(1.08); }
    .dk-btn-warning { background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; box-shadow:0 2px 12px rgba(245,158,11,0.3), inset 0 1px 0 rgba(255,255,255,0.18); }
    .dk-btn-outline { background:rgba(148,163,184,0.05); color:#94a3b8; border:1px solid rgba(148,163,184,0.2); }
    .dk-btn-outline:hover { background:rgba(99,102,241,0.1); color:#e2e8f0; border-color:rgba(129,140,248,0.35); }

    /* ---------- Misc ---------- */
    .dk-badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:9999px; font-size:11px; font-weight:600; }
    .dk-alert-success { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; padding:12px 16px; border-radius:12px; margin-bottom:24px; font-size:14px; -webkit-backdrop-filter:blur(10px); backdrop-filter:blur(10px); }
    .dk-alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; padding:12px 16px; border-radius:12px; margin-bottom:24px; font-size:14px; -webkit-backdrop-filter:blur(10px); backdrop-filter:blur(10px); }
    .dk-label { color:#94a3b8; font-size:14px; font-weight:500; margin-bottom:6px; display:block; }
    .dk-heading { color:#f1f5f9; }
    .dk-text-muted { color:#64748b; }
    .dk-text { color:#cbd5e1; }
    .dk-divider { border-top:1px solid rgba(148,163,184,0.1); margin:12px 0; }

    .dk-stat-card {
        background: linear-gradient(160deg, rgba(30, 38, 66, 0.72), rgba(17, 23, 43, 0.66));
        -webkit-backdrop-filter: blur(14px);
        backdrop-filter: blur(14px);
        border: 1px solid var(--dk-border);
        border-radius: 18px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        box-shadow: 0 8px 32px rgba(2, 6, 23, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .dk-stat-card::before { content:''; position:absolute; top:0; left:20%; right:20%; height:1px; background:linear-gradient(90deg, transparent, rgba(129,140,248,0.5), transparent); }
    .dk-stat-card:hover { transform:translateY(-3px); border-color:rgba(129,140,248,0.3); box-shadow:0 14px 44px rgba(2,6,23,0.5), 0 0 30px rgba(99,102,241,0.1); }
    .dk-stat-card > div { min-width:0; }
    .dk-stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

    /* ---------- Layout shell (struktur TIDAK berubah) ---------- */
    * { box-sizing: border-box; }
    .dk-shell { min-height:100vh; display:flex; }
    .dk-sidebar {
        width:260px;
        background: linear-gradient(180deg, rgba(12, 17, 34, 0.85) 0%, rgba(16, 22, 42, 0.85) 100%);
        -webkit-backdrop-filter: blur(18px);
        backdrop-filter: blur(18px);
        flex-shrink:0;
        border-right:1px solid rgba(129, 140, 248, 0.1);
        display:flex;
        flex-direction:column;
    }
    .dk-main { flex:1; overflow:auto; min-width:0; }
    .dk-content { padding:32px; animation: dkFadeIn 0.45s ease both; }
    .dk-topbar { display:none; }
    .dk-overlay { display:none; }

    @keyframes dkFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive stat / card grids */
    .dk-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); }
    .dk-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); }
    .dk-grid-2 { display:grid; grid-template-columns:repeat(2,1fr); }
    .dk-grid-5 { display:grid; grid-template-columns:repeat(5,1fr); }

    @media (max-width:1024px) {
        .dk-grid-4 { grid-template-columns:repeat(2,1fr) !important; }
        .dk-grid-3 { grid-template-columns:repeat(2,1fr) !important; }
        .dk-grid-5 { grid-template-columns:repeat(3,1fr) !important; }
    }

    @media (max-width:768px) {
        .dk-sidebar { position:fixed; top:0; left:0; bottom:0; z-index:50; transform:translateX(-100%); transition:transform 0.25s ease; overflow-y:auto; background:rgba(11, 16, 32, 0.97); }
        .dk-sidebar.dk-open { transform:translateX(0); box-shadow: 20px 0 60px rgba(2,6,23,0.6); }
        .dk-topbar { display:flex; align-items:center; gap:12px; padding:12px 16px; background:rgba(10, 15, 30, 0.85); -webkit-backdrop-filter:blur(14px); backdrop-filter:blur(14px); border-bottom:1px solid rgba(129,140,248,0.1); position:fixed; top:0; left:0; right:0; z-index:30; height:60px; }
        .dk-content { padding:78px 16px 20px; }
        .dk-overlay.dk-open { display:block; position:fixed; inset:0; background:rgba(2,6,23,0.6); -webkit-backdrop-filter:blur(3px); backdrop-filter:blur(3px); z-index:40; }
        .dk-table { display:block; overflow-x:auto; white-space:nowrap; }
        .dk-table th, .dk-table td { padding:12px 16px; }
    }

    @media (max-width:640px) {
        .dk-grid-4 { grid-template-columns:1fr !important; }
        .dk-grid-3 { grid-template-columns:1fr !important; }
        .dk-grid-2 { grid-template-columns:1fr !important; }
        .dk-grid-5 { grid-template-columns:1fr !important; }
    }

    /* Hormati preferensi pengguna yang sensitif terhadap gerakan */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
    }
</style>
