#!/bin/bash
# ============================================================
# RESTORE OLD UI — mengembalikan tampilan lama (pre-glassmorphism)
# ============================================================
# UI lama disimpan di git tag: ui-v1-original
#
# Cara pakai (di komputer development):
#   bash scripts/restore-old-ui.sh
#   git push origin <branch>            # lalu merge & deploy seperti biasa
#
# Cara pakai (langsung di server, dari ~/vibetool-src):
#   bash scripts/restore-old-ui.sh
#   lalu jalankan ulang: bash ~/pull-vibetool.sh  TIDAK diperlukan —
#   script ini hanya mengubah working tree lokal. Untuk produksi,
#   commit hasil restore ke main lalu deploy normal via pull-vibetool.sh.
#
# Script ini HANYA menyentuh file UI yang diubah oleh redesign,
# tidak menyentuh controller/route/fungsi apa pun.
set -e

TAG="ui-v1-original"

# Daftar file UI yang diubah oleh redesign glassmorphism.
UI_FILES=(
    resources/views/layouts/public.blade.php
    resources/views/layouts/dashboard.blade.php
    resources/views/layouts/admin.blade.php
    resources/views/layouts/guest.blade.php
    resources/views/home.blade.php
)

# File BARU yang ditambahkan redesign (harus dihapus saat restore).
NEW_FILES=(
    resources/views/layouts/partials/dk-theme.blade.php
)

echo "=== Restore UI lama dari tag: $TAG ==="

git fetch --tags origin 2>/dev/null || true

if ! git rev-parse -q --verify "refs/tags/$TAG" >/dev/null; then
    echo "ERROR: tag $TAG tidak ditemukan. Jalankan: git fetch --tags origin"
    exit 1
fi

echo ""
echo "[1/3] Mengembalikan file UI lama..."
git checkout "$TAG" -- "${UI_FILES[@]}"
for f in "${UI_FILES[@]}"; do echo "  restored: $f"; done

echo ""
echo "[2/3] Menghapus file yang ditambahkan redesign..."
for f in "${NEW_FILES[@]}"; do
    if [ -f "$f" ]; then
        git rm -q --ignore-unmatch "$f" 2>/dev/null || rm -f "$f"
        echo "  removed : $f"
    fi
done

echo ""
echo "[3/3] Bersihkan cache view (jika artisan tersedia)..."
php artisan view:clear 2>/dev/null || echo "  (lewati — artisan tidak tersedia di sini)"

echo ""
echo "============================================"
echo "SELESAI. UI lama sudah dipulihkan di working tree."
echo "Langkah berikutnya:"
echo "  git add -A && git commit -m 'revert: kembalikan UI lama (ui-v1-original)'"
echo "  git push, merge ke main, lalu deploy: bash ~/pull-vibetool.sh"
echo "============================================"
