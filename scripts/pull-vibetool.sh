#!/bin/bash
# VibeTool deploy pull script.
# Run after merging a PR to main on GitHub.
# Workflow: pulls latest from origin/main into ~/vibetool-src/,
# then rsync changed files into ~/domains/vibetool.id/ (preserving .env, storage, vendor, public_html structure).
#
# Canonical location of this file in the repo: scripts/pull-vibetool.sh
# Canonical location on the server:           ~/pull-vibetool.sh
# After editing in the repo, copy the updated version onto the server:
#   scp -P 65002 scripts/pull-vibetool.sh u295282884@145.79.14.140:~/pull-vibetool.sh
# or paste it manually over an SSH session.
set -e

SRC="$HOME/vibetool-src"
APP="$HOME/domains/vibetool.id"

echo "=== VibeTool Git Pull Deploy ==="
echo "Source: $SRC"
echo "App   : $APP"

if [ ! -d "$SRC/.git" ]; then
    echo "ERROR: $SRC is not a git repo. Re-run setup (Step 4 — git clone)."
    exit 1
fi

if [ ! -f "$APP/artisan" ]; then
    echo "ERROR: $APP is not a Laravel app (artisan not found)."
    exit 1
fi

echo ""
echo "[1/6] git fetch + reset to origin/main..."
cd "$SRC"
git fetch --all --prune
CURRENT=$(git rev-parse HEAD)
git reset --hard origin/main
NEW=$(git rev-parse HEAD)
if [ "$CURRENT" = "$NEW" ]; then
    echo "Already up to date with origin/main ($NEW). Continuing anyway to refresh caches."
else
    echo "Updated $CURRENT -> $NEW"
    echo ""
    echo "Changed files in this update:"
    git diff --name-only "$CURRENT" "$NEW" | head -50
fi

echo ""
echo "[2/6] Sync app files (excluding env/storage/vendor/public/public_html)..."
rsync -av --delete \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='.gitignore' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='public' \
    --exclude='public_html' \
    --exclude='storage/app/*' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/framework/testing/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='pull-vibetool.sh' \
    "$SRC/" "$APP/"

echo ""
echo "[3/6] Sync public/ from source -> public_html/ (web root)..."
rsync -av --delete \
    --exclude='build' \
    --exclude='storage' \
    "$SRC/public/" "$APP/public_html/"

echo ""
echo "[4/6] Ensure public_html/storage -> storage/app/public symlink..."
# Hostinger DocumentRoot is public_html/, so Laravel's storage:link target lives at
# public_html/storage and must point at storage/app/public. This symlink can go missing
# after fresh deploys or restores; we re-assert it every pull to stay self-healing.
STORAGE_SYMLINK="$APP/public_html/storage"
STORAGE_TARGET="$APP/storage/app/public"
mkdir -p "$STORAGE_TARGET"
if [ -L "$STORAGE_SYMLINK" ]; then
    ln -sfn "$STORAGE_TARGET" "$STORAGE_SYMLINK"
    echo "Refreshed symlink: $STORAGE_SYMLINK -> $STORAGE_TARGET"
elif [ -e "$STORAGE_SYMLINK" ]; then
    echo "ERROR: $STORAGE_SYMLINK exists and is NOT a symlink."
    echo "       Inspect its contents, back them up if needed, remove the path, then re-run."
    exit 1
else
    ln -sfn "$STORAGE_TARGET" "$STORAGE_SYMLINK"
    echo "Created symlink: $STORAGE_SYMLINK -> $STORAGE_TARGET"
fi
ls -la "$STORAGE_SYMLINK"

echo ""
echo "[5/6] Composer install (production deps only)..."
cd "$APP"
if [ -f composer.lock ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress
else
    echo "composer.lock missing - skipping"
fi

echo ""
echo "[6/7] Clear + rebuild Laravel caches..."
php artisan view:clear || true
php artisan route:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "[7/7] Check for pending migrations..."
# Capture status output, tolerate non-zero exit (e.g. no migrations table yet).
MIGRATION_STATUS="$(php artisan migrate:status 2>&1 || true)"
PENDING_LINES="$(echo "$MIGRATION_STATUS" | grep -E 'Pending' || true)"

if [ -n "$PENDING_LINES" ]; then
    echo "Pending migrations detected:"
    echo "$PENDING_LINES"
    echo ""

    # Allow non-interactive auto-confirm via env var, e.g.:
    #   AUTO_MIGRATE=1 bash ~/pull-vibetool.sh
    # AUTO_MIGRATE=0 explicitly skips without prompting.
    if [ "${AUTO_MIGRATE:-}" = "1" ]; then
        echo "AUTO_MIGRATE=1 -> running migrations without prompt."
        ANSWER="y"
    elif [ "${AUTO_MIGRATE:-}" = "0" ]; then
        echo "AUTO_MIGRATE=0 -> skipping migrations."
        ANSWER="n"
    else
        # Interactive prompt. Default = N (safer).
        printf "Run 'php artisan migrate --force' now? [y/N] "
        read -r ANSWER < /dev/tty || ANSWER=""
    fi

    case "$ANSWER" in
        y|Y|yes|YES)
            echo ""
            echo "Running migrations..."
            php artisan migrate --force
            ;;
        *)
            echo ""
            echo "Skipped. Jalankan manual nanti dengan:"
            echo "  cd $APP && php artisan migrate --force"
            ;;
    esac
else
    echo "No pending migrations."
fi

echo ""
echo "============================================"
echo "PULL SELESAI."
echo "============================================"
echo "HEAD: $NEW"
echo "Akses: https://vibetool.id"
echo "Hard refresh browser (Ctrl+Shift+R) untuk lihat perubahan UI."
