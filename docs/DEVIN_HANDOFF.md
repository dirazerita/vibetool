# VibeTool — Devin Handoff Notes

**Audience:** any future Devin session continuing work on VibeTool.
**Last updated:** 2026-05-28
**Status:** production, live at https://vibetool.id

Read this top-to-bottom before doing anything on production. Skim the [Onboarding Checklist](#onboarding-checklist-for-new-devin) for the fast path.

---

## TL;DR — what works today

| Capability | Status | Notes |
|---|---|---|
| Public site (landing, register, login) | LIVE | https://vibetool.id |
| Admin dashboard (`/admin`) | LIVE | role-based middleware, dark theme |
| Member dashboard (`/dashboard`) | LIVE | gated by member activation |
| Database (MySQL, migrated + seeded) | LIVE | 3 sample products seeded |
| HTTPS / SSL | LIVE | Hostinger free SSL (auto-renew) |
| Cron / Laravel scheduler | configured | runs every 30 min via Hostinger Cron Jobs |
| Manual bank transfer payment | enabled | bank details stored in Settings, members upload proof, admin marks paid |
| Telegram bot notifications | enabled | bot token + chat ID + webhook all configured by user via `/admin/settings` |
| Admin Profil Saya (`/admin/profile`) | available | PR #68 — change own name/email/password from UI |
| Git-pull production workflow | available | `bash ~/pull-vibetool.sh` on server (see [Git Workflow](#git-workflow-for-production-updates)) |
| Xendit (online payment) | NOT used | user chose manual transfer instead |

---

## Architecture

- **Framework:** Laravel 11 (PHP 8.2+ required; deployed on PHP 8.3.30)
- **Asset bundling:** Vite (CSS + JS). Pre-built locally and shipped — server has no Node.
- **Frontend:** Blade templates + Tailwind CSS + Alpine.js (inline). Dark theme with custom `.dk-*` utility classes defined in `resources/views/layouts/admin.blade.php` (`<style>` block).
- **Backend roles:** `admin` middleware (App\Http\Middleware\AdminMiddleware) gates `/admin/*` routes. Members gated by separate activation flag on `users.is_active`.
- **DB:** MySQL on the same Hostinger plan (localhost).
- **Auth:** Laravel session-based, default `App\Http\Controllers\Auth\*`.

### Key routes

| Route | Purpose |
|---|---|
| `/` | Public home / landing |
| `/register`, `/login` | Auth |
| `/dashboard` | Member dashboard |
| `/dashboard/products` | Member: browse products |
| `/dashboard/purchases` | Member: order history |
| `/checkout/{product}` | Manual checkout flow |
| `/webhook/telegram/{token}` | Telegram bot callback URL |
| `/webhook/xendit` | (disabled — Xendit not used) |
| `/admin` | Admin overview |
| `/admin/products`, `/admin/orders`, `/admin/members`, `/admin/settings`, `/admin/profile` | Admin functions |

Full routes: see `routes/web.php`.

---

## Deployment Environment — Hostinger Shared

### Host info

| Item | Value |
|---|---|
| Hostname | `145.79.14.140` |
| SSH port | `65002` (non-standard — set in client config) |
| SSH user | `u295282884` |
| Domain | `vibetool.id` |
| App root | `/home/u295282884/domains/vibetool.id/` |
| Web root (Apache DocumentRoot) | `/home/u295282884/domains/vibetool.id/public_html/` |
| PHP binary | `/usr/bin/php` (PHP 8.3.30) |
| Data center | Indonesia (Jakarta) |
| Plan | Hostinger Premium |
| Control panel | hPanel (https://hpanel.hostinger.com) |

### Directory layout on server

```
/home/u295282884/
├── domains/
│   └── vibetool.id/                  # Laravel app root (current working copy)
│       ├── app/                      # source from repo
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── public/         -> public_html  (symlink, Laravel-internal compat)
│       ├── public_html/              # Apache DocumentRoot (= Laravel's public/ contents)
│       │   ├── index.php
│       │   ├── .htaccess
│       │   ├── logo.png
│       │   ├── build/                # Vite-built CSS/JS assets
│       │   └── storage  -> ../storage/app/public   (Laravel storage:link)
│       ├── resources/
│       ├── routes/
│       ├── storage/
│       │   ├── app/
│       │   ├── framework/{cache,sessions,views}/
│       │   └── logs/
│       ├── vendor/                   # composer deps (installed in place)
│       ├── .env                      # PRODUCTION env — DO NOT commit, DO NOT delete
│       ├── artisan
│       └── composer.json
├── vibetool-src/                     # clean git clone of rynz2024/vibetool, used by pull-vibetool.sh
└── pull-vibetool.sh                  # deploy script — see Git Workflow section
```

**Why `public_html/` is the real dir and `public/` is a symlink:** Hostinger Apache uses `public_html/` as DocumentRoot for the domain. To match Laravel's convention (which expects `public/` to be the document root), we keep `public/` as a symlink so any internal Laravel reference still works.

### Credentials — where to find them

**NEVER paste credentials in this file or commit them.** Locations:

- **SSH password:** Hostinger hPanel → SSH Access; or the user's password manager.
- **Hostinger account password:** the user's password manager.
- **Database password:** in `/home/u295282884/domains/vibetool.id/.env` on server. Also stored in hPanel → Databases.
- **Admin login (`admin@vibetool.id`):** set by user via the script described in [Resetting Admin Password](#resetting-admin-password). Stored only in user's password manager.
- **Telegram bot token + chat ID:** stored in DB `app_settings` table (via `/admin/settings`).
- **GitHub PAT (for assistant-side pushes):** stored in Devin secret `GITHUB_PAT_RYNZ2024` (org scope).
- **GitHub deploy key on server:** `~/.ssh/github-vibetool` (ed25519, read-only access to `rynz2024/vibetool`). Public key is added at https://github.com/rynz2024/vibetool/settings/keys.

If credentials are missing, ASK the user — don't try to reset them via reckless means.

### .env on server (key vars)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vibetool.id
APP_KEY=<base64:... already set>

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u295282884_vibetool
DB_USERNAME=u295282884_rynz
DB_PASSWORD=<see hPanel>

MAIL_*=...   # not configured yet — emails won't send. Member activation/order flow uses WhatsApp + Telegram instead.

XENDIT_SECRET_KEY=  # blank — not used
XENDIT_WEBHOOK_TOKEN=  # blank — not used
```

---

## Git Workflow for Production Updates

The server has a deploy script at `/home/u295282884/pull-vibetool.sh` that handles git pull + rsync + cache rebuild.

### Workflow (per code change)

1. Devin creates a PR against `main` of `rynz2024/vibetool` (via `git_create_pr` after fetching the template).
2. User reviews & merges the PR on GitHub.
3. User SSHes to server and runs:
   ```bash
   bash ~/pull-vibetool.sh
   ```
4. Done. ~5-15 seconds. Caches auto-clear + rebuild.

### What pull-vibetool.sh does (overview)

1. `git fetch + git reset --hard origin/main` in `~/vibetool-src/`
2. `rsync` source → `~/domains/vibetool.id/`, EXCLUDING:
   - `.env`, `.env.*` — production env preserved
   - `storage/{app,logs,framework}/*` — user data preserved
   - `bootstrap/cache/*.php` — Laravel cache (regenerated at step 5)
   - `vendor/` — composer-managed
   - `node_modules/`
   - `public/`, `public_html/` — handled separately in step 3
3. `rsync` source `public/` → `~/domains/vibetool.id/public_html/`, EXCLUDING `build/` (Vite assets preserved) and `storage/` (Laravel storage symlink preserved)
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan view:clear && route:clear && config:clear && cache:clear`
6. `php artisan config:cache && route:cache && view:cache`

**What the script does NOT do:**
- Run migrations (commented out for safety — uncomment in script or run manually if a PR ships a migration)
- Rebuild Vite assets (the server has no Node — see [Asset Builds](#asset-builds))

### When the change includes a migration

After `bash ~/pull-vibetool.sh` succeeds, instruct the user to run:
```bash
cd ~/domains/vibetool.id/
php artisan migrate --force
```

Always preview migration plan before running:
```bash
php artisan migrate --pretend
```

### Asset builds (Vite — CSS/JS)

The server doesn't have Node, so `npm run build` can't run there. If a PR changes anything under `resources/css/` or `resources/js/`, you must:

1. Build assets locally:
   ```bash
   npm install
   npm run build
   ```
2. Commit the built `public/build/` to the branch (it's `.gitignored` by default — adjust `.gitignore` or use a separate deploy branch).
3. Or: build a tarball with just `public_html/build/` contents and provide to user for manual upload.

Most PRs so far have been Blade-only (no CSS/JS changes), so this hasn't been needed. If a PR changes Tailwind config or adds new JS, plan for asset shipping.

---

## Common Operations

### Connect to the server

```bash
ssh -p 65002 u295282884@145.79.14.140
```

(Password from password manager. SSH key auth not configured for assistant access.)

### Resetting Admin Password

Hostinger shared hosting disables `shell_exec()`, which breaks `php artisan tinker` (PsySH needs terminal-width detection). Use a standalone PHP script instead:

```bash
cd ~/domains/vibetool.id/

cat > /tmp/upd.php <<'EOF'
<?php
if ($argc < 3) { echo "Usage: php upd.php <new-email> <new-password>\n"; exit(1); }
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = \App\Models\User::where('email','admin@vibetool.id')->first();
if (!$u) { echo "Admin user not found\n"; exit(1); }
$u->email = $argv[1];
$u->password = bcrypt($argv[2]);
$u->save();
echo "Updated: {$u->email} (id: {$u->id})\n";
EOF

php /tmp/upd.php "<new-email>" "<new-password>"

rm /tmp/upd.php
```

**Or:** the admin can change their own password via `/admin/profile` (PR #68 added that). Use the SSH method only as a last resort or for the very first password setup.

### Tail Laravel logs

```bash
tail -f ~/domains/vibetool.id/storage/logs/laravel.log
```

### Clear all caches manually

```bash
cd ~/domains/vibetool.id/
php artisan optimize:clear
# equivalent to view:clear + route:clear + config:clear + cache:clear + compiled:clear + event:clear
```

### Run a one-off artisan command (without tinker)

Create `/tmp/run.php` with bootstrap code like in [Resetting Admin Password](#resetting-admin-password), then `php /tmp/run.php`.

### Test the cron job

```bash
cd ~/domains/vibetool.id/
php artisan schedule:run
# Expected: "No scheduled commands are ready to run." (if no scheduled tasks defined)
```

The cron is configured at: hPanel → Tingkat lanjut → Cron Jobs.
- Command: `/usr/bin/php /home/u295282884/domains/vibetool.id/artisan schedule:run`
- Schedule: `0,30 * * * *` (every 30 minutes — Hostinger Premium constraint)

### Database access via hPanel

hPanel → Databases → MySQL Databases → phpMyAdmin (one-click). Database name: `u295282884_vibetool`. User: `u295282884_rynz`.

---

## Known Quirks / Workarounds

### 1. `shell_exec()` is disabled on Hostinger shared hosting
- **Impact:** `php artisan tinker` fails with "Call to undefined function shell_exec()" because PsySH needs it for terminal-width detection.
- **Workaround:** standalone PHP script (see [Resetting Admin Password](#resetting-admin-password)).

### 2. `public_html/` is the web root (not `public/`)
- Hostinger expects `public_html/` as DocumentRoot. The deploy structure renames Laravel's `public/` to `public_html/` and creates a `public/` symlink.
- `pull-vibetool.sh` handles this automatically via separate rsync targets.

### 3. Devin VM cannot SSH outbound to Hostinger
- The org's Devin VMs are network-restricted and cannot connect to `145.79.14.140:65002`.
- All server-side commands must be executed by the user (via their local SSH client).
- Workflow: Devin provides bash snippets, user pastes them into PowerShell/Terminal.

### 4. Cron interval limited to 15-30 min on Hostinger Premium
- Standard Laravel cron expects `* * * * *` (every minute). Hostinger Premium limits to ~15-30 min minimum.
- Currently configured: every 30 min. Adequate because no real scheduled tasks exist yet.
- If `everyMinute()` tasks are added, evaluate: upgrade plan, or use external cron service like cron-job.org hitting a webhook endpoint.

### 5. No Node.js on server (Vite build is local-only)
- See [Asset builds](#asset-builds-vite--cssjs).

### 6. No email service configured
- `MAIL_*` env vars are blank. Password reset emails / email notifications WILL NOT send.
- Member activation flow currently uses WhatsApp + Telegram instead.
- If email is needed: configure SMTP (Hostinger has free SMTP) and set `MAIL_*` in `.env`.

### 7. Settings form validation — Telegram checkbox requires complete config
- If user checks "Aktifkan Notifikasi Telegram" without filling Bot Token + Chat ID, save will fail validation.
- Currently: enabled with valid token + chat ID, webhook installed.

### 8. Logo image is low resolution
- `public/logo.png` is small (≈250×80px), gets visibly upscaled in 90px-tall sidebar.
- User was offered 3 higher-res variants (full / landscape / monogram) but chose to skip.
- If user changes their mind: see commit history of attempted variants on Devin session 2026-05-28.

---

## Open Tasks (as of handoff)

- [ ] **End-to-end test alur produksi** — register dummy member, activate, checkout manual, upload proof, verify Telegram notifications, admin marks paid, member gets product access. User wanted to do this themselves, may need Devin assist.
- [ ] **Email service** — configure SMTP if needed for transactional emails (password reset, order confirmations).
- [ ] **Logo upgrade** — user skipped for now but may revisit.
- [ ] **Migrations runner in pull script** — currently commented out for safety. Decide if it should auto-run (with backup) or require manual step.
- [ ] **Backup strategy** — no automated DB backup configured yet. Hostinger has a daily backup plan, but not verified to be active.
- [ ] **Add scheduled tasks** — Laravel scheduler currently has no tasks defined. Candidates: auto-expire pending orders after N days, periodic cleanup of expired download tokens, daily summary to Telegram, etc.

## Open Tasks — out of scope so far

- Xendit integration (user chose manual)
- Multi-language support
- Mobile-responsive audit (some pages may have issues on small screens)
- Performance audit / optimization

---

## File Locations Reference

| Path | Purpose |
|---|---|
| `app/Http/Controllers/Admin/*` | Admin controllers |
| `app/Http/Controllers/Admin/ProfileController.php` | Admin self-profile edit (PR #68) |
| `app/Models/*` | Eloquent models — User, Product, Order, License, Payment, Setting, etc. |
| `app/Services/TelegramService.php` (if exists) | Telegram bot wrapper |
| `database/migrations/*` | DB schema |
| `database/seeders/DatabaseSeeder.php` | Default admin + 3 sample products |
| `resources/views/layouts/admin.blade.php` | Admin layout — sidebar, `.dk-*` CSS utilities |
| `resources/views/layouts/dashboard.blade.php` | Member layout |
| `resources/views/layouts/public.blade.php` | Public layout (navbar) |
| `resources/views/admin/settings.blade.php` | Pengaturan page — manual payment, Telegram |
| `resources/views/admin/profile.blade.php` | Profil Saya — name, email, password (PR #68) |
| `routes/web.php` | All routes |
| `routes/console.php` | Where scheduled tasks should be registered |
| `.env` (on server, NOT in repo) | Production env |

---

## Onboarding Checklist for new Devin

When starting a new session on this project:

1. **Read this file fully**, especially [Known Quirks](#known-quirks--workarounds) and [Open Tasks](#open-tasks-as-of-handoff).
2. **Clone the repo** if not present:
   ```bash
   gh repo clone rynz2024/vibetool ~/repos/vibetool
   ```
3. **Don't try to SSH into Hostinger** — Devin VMs cannot. Have the user run commands locally.
4. **For any code change:**
   - Create a feature branch (`devin/<timestamp>-<short-name>`)
   - Push, create PR with template, get user approval, user merges
   - User runs `bash ~/pull-vibetool.sh` to apply on prod
5. **For credentials:** check user's password manager (ask) or look in `.env` on server (only the user can read it).
6. **Before doing destructive ops** (DB resets, migration rollbacks, force pushes): confirm with user first.
7. **Existing PRs:**
   - #68 — Admin Profil Saya + dark-theme checkbox CSS (merged)
   - #69 — sidebar logo bigger + centered (merged)
8. **Skills / playbooks:** if the user has standing playbooks, run them. If none, follow this doc.

---

## Contact / escalation

- **Primary stakeholder:** andieamikha (GitHub: andieamikha, email: andieamikha@gmail.com)
- **Hosting account holder:** same as above
- **Default admin login email:** `admin@vibetool.id`
- **Default WhatsApp for member activation:** `082312181216` (configured in `/admin/settings`)

If the user is unresponsive and something is blocking production, do not make irreversible changes. Document the blocker, leave the system in a stable state, message via `message_user` with full context.

---

_Update this file via PR whenever you add a significant capability, change a workflow, or discover a new quirk. Keep it accurate so the next Devin doesn't have to rediscover what we already know._
