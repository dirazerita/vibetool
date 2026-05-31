# Panduan Integrasi Lisensi — Vibetool (PRODIG)

Dokumen ini ditujukan untuk **developer / AI** yang membangun software atau aplikasi yang dijual / dibagikan lewat marketplace **Vibetool** (https://vibetool.id). Setelah baca dokumen ini, integrator **tidak perlu** membuka source code website — semua yang dibutuhkan ada di sini.

> **Bahasa request/response selalu JSON.** Semua endpoint ada di `https://vibetool.id/api/*` dan **tidak butuh API key** (publik, di-rate-limit di sisi server).

---

## 1. Konsep singkat

Vibetool menjual dua macam produk software:

| Tipe | `product_type` | Cara user dapat akses | Cara software memvalidasi user |
|---|---|---|---|
| **Software Berbayar** | `software` | User bayar di Vibetool → dapat **kode lisensi unik** lewat email + dashboard | Software minta user input **license key**, lalu cek ke endpoint `/api/license/validate` |
| **Software Gratis** | `free` | User klaim gratis di Vibetool (perlu akun aktif) | Software pakai **email + password akun Vibetool** user, lalu cek ke endpoint `/api/auth/validate-member` |

**Pilih cara integrasi sesuai tipe produk:**
- Software bayar → **Mode A (License Key)** — Section 3.
- Software gratis → **Mode B (Login Akun Vibetool)** — Section 4.

---

## 2. Daftar endpoint (ringkas)

Base URL produksi: `https://vibetool.id/api`

| Endpoint | Method | Tujuan | Mode |
|---|---|---|---|
| `/license/validate` | `POST` | Validasi kode lisensi software bayar | A |
| `/auth/validate-member` | `POST` | Validasi email+password member untuk software gratis | B |
| `/auth/register` | `POST` | (Opsional) Daftarkan member baru dari dalam software | B |
| `/setting/whatsapp-admin` | `GET` | Ambil nomor WA admin untuk tombol "Hubungi Admin" | A / B |

Semua endpoint di-rate-limit per IP (lihat detail per endpoint).

**Outbound webhook** (Vibetool → software builder): selain endpoint di atas, software builder bisa konfigurasi **webhook URL** di admin produk untuk menerima notifikasi `license.issued`, `license.revoked`, `license.renewed`. Lihat [section 3.7](#37-webhook-events-opsional-untuk-server-software-builder).

---

## 3. Mode A — Software Berbayar (License Key)

### 3.1 Flow di software

```
[User pasang software]
        │
        ▼
[Software minta input kode lisensi]
        │ user paste key dari email / dashboard Vibetool
        ▼
[Software POST ke /api/license/validate]
        │
        ▼
   ┌────────┴────────┐
   │ valid: true     │ → simpan key lokal (file/registry) → buka aplikasi
   │ valid: false    │ → tampilkan pesan error + tombol "Hubungi Admin" (Section 5)
   └─────────────────┘
```

**Best practice software builder:**
- Simpan license key di file/registry lokal setelah validasi pertama berhasil.
- Re-validate ke server **maksimal sekali per startup** (atau per hari) — jangan tiap menit, kena rate-limit & boros bandwidth user.
- Kalau offline, izinkan jalan dengan key cache; tampilkan warning kalau cache > 7 hari.
- Saat menampilkan error, **tampilkan `message` apa adanya** dari response — sudah dalam bahasa Indonesia yang user-friendly.

### 3.2 Endpoint detail

**`POST /api/license/validate`**

Headers wajib:
```
Content-Type: application/json
Accept: application/json
```

Body (JSON):
```json
{
  "key": "ABCD-1234-EFGH-5678",
  "product_slug": "telegram-blaster-pro",
  "device_fingerprint": "a1b2c3d4...",
  "device_label": "Laptop Budi"
}
```

| Field | Wajib | Tipe | Keterangan |
|---|---|---|---|
| `key` | ya | string | Kode lisensi yang di-paste user. |
| `product_slug` | tidak (tapi **disarankan**) | string **atau** array | Slug produk dari Vibetool. Jika diisi, server pastikan lisensi memang untuk salah satu produk yang dicantumkan (cegah user pakai key produk lain). Lihat 3.2.1 untuk multi-produk. |
| `device_fingerprint` | tidak (tapi **disarankan**) | string max 128 | Hash identitas device. Lihat Section 3.6 untuk cara generate. Kalau dikirim, server akan track device dan menolak kalau jumlah device melebihi `max_devices` produk. |
| `device_label` | tidak | string max 64 | Label human-friendly device (mis. `"Laptop Budi - Windows 11"`) supaya admin gampang identifikasi waktu reset. |

**Slug produk** = bagian terakhir URL landing page produk di Vibetool, misal: `https://vibetool.id/p/telegram-blaster-pro` → slug = `telegram-blaster-pro`. **Hardcode** slug ini di software Anda.

### 3.2.1 Multi-produk per software (opsional)

Kalau satu software bisa di-unlock oleh beberapa produk berbeda (mis. paket Basic / Pro / Suite, atau seri produk yang share kode software yang sama), kirim `product_slug` sebagai **array** alih-alih string tunggal:

```json
{
  "key": "ABCD-1234-EFGH-5678",
  "product_slug": ["tools-basic", "tools-pro", "tools-suite"]
}
```

Server akan validasi kalau lisensi cocok dengan **salah satu** slug di array (OR matching). Response sukses tetap sama, dengan tambahan `license.matched_slug` di response root supaya software tahu user beli paket yang mana:

```json
{
  "valid": true,
  "license": {
    "matched_slug": "tools-pro",
    "product": { "slug": "tools-pro", "title": "Tools Pro", ... },
    ...
  }
}
```

Backward compat: `product_slug` sebagai string tunggal (cara lama) tetap berfungsi tanpa perubahan, dan response selalu menyertakan `license.matched_slug` (sama dengan `license.product.slug`).

**Pakai array kalau** Anda mau:
- Software "Tools Suite" yang menerima lisensi `tools-basic` (fitur terbatas) ATAU `tools-pro` (fitur full) — software cek `license.matched_slug` untuk gating feature.
- Migrasi produk: produk lama `legacy-foo` rename jadi `foo` — software accept keduanya selama transisi.
- Series produk: `course-v1`, `course-v2`, `course-v3` di-validasi oleh 1 reader app.

Untuk request `form-urlencoded` yang sulit kirim array (mis. PHP cURL tanpa array bracket), Anda bisa kirim **JSON-encoded array sebagai string**: `product_slug=["tools-basic","tools-pro"]` — server akan auto-detect dan parse.

### 3.3 Response sukses (HTTP 200)

```json
{
  "valid": true,
  "message": "Lisensi valid.",
  "license": {
    "key": "ABCD-1234-EFGH-5678",
    "product": {
      "id": 7,
      "title": "Telegram Blaster Pro",
      "slug": "telegram-blaster-pro",
      "max_devices": 2
    },
    "matched_slug": "telegram-blaster-pro",
    "user": {
      "id": 42,
      "name": "Budi",
      "email": "budi@example.com"
    },
    "assigned_at": "2026-05-20T10:23:00+00:00",
    "expires_at": "2027-05-20T10:23:00+00:00",
    "is_lifetime": false
  },
  "device": {
    "fingerprint": "a1b2c3d4...",
    "label": "Laptop Budi",
    "first_seen_at": "2026-05-30T07:34:00+00:00",
    "last_seen_at": "2026-05-31T08:12:00+00:00"
  },
  "max_devices": 2
}
```

| Field penting | Cara pakai |
|---|---|
| `license.user.name` / `email` | Tampilkan di pojok kanan atas software ("Halo, Budi"). |
| `license.expires_at` | Tampilkan tanggal kedaluwarsa di About / Settings software. `null` artinya lifetime. |
| `license.is_lifetime` | `true` = lifetime, jangan tampilkan expiry. |
| `license.product.max_devices` / `max_devices` | Jumlah maksimum device yang boleh dipakai 1 lisensi. Tampilkan di About kalau perlu. |
| `license.matched_slug` | Slug produk yang cocok dengan lisensi. Sama dengan `license.product.slug`. Useful kalau Anda kirim `product_slug` array di request (lihat 3.2.1) untuk tahu paket mana yang user beli. |
| `device.*` | Info device yang baru di-register/touch (hanya ada kalau `device_fingerprint` dikirim). |

> Kalau `device_fingerprint` **tidak** dikirim di request, response sukses **tidak** akan punya field `device` / `max_devices`. Existing software lama tanpa device tracking tetap kompatibel.

### 3.4 Response gagal

| HTTP | `error` | Kapan muncul | Yang harus software lakukan |
|---|---|---|---|
| 422 | (validasi Laravel) | `key` kosong | Minta user paste key. |
| 404 | `license_not_found` | Key salah / belum dialokasikan / bukan untuk produk ini | Tampilkan pesan + minta user copy ulang dari email/dashboard Vibetool. |
| 403 | `license_expired` | Lisensi sudah lewat tanggal kedaluwarsa | Tampilkan pesan + ajak user perpanjang (link ke landing page produk). |
| 403 | `device_limit_exceeded` | Lisensi valid tapi sudah dipakai di N device (N >= `max_devices`) dan request datang dari device baru | Tampilkan pesan + tombol **Hubungi Admin** untuk minta reset. Response body berisi list `devices` yang sudah terdaftar supaya user bisa identifikasi. |

Contoh response gagal:
```json
{
  "valid": false,
  "error": "license_not_found",
  "message": "Kunci lisensi tidak ditemukan atau belum dialokasikan."
}
```

Contoh response `device_limit_exceeded`:
```json
{
  "valid": false,
  "error": "device_limit_exceeded",
  "message": "Lisensi ini sudah dipakai di 2 device (batas maksimum). Hubungi admin untuk reset device kalau ganti perangkat.",
  "license": { "... seperti format normal ...": null },
  "devices": [
    { "fingerprint": "abc...", "label": "PC Kantor", "first_seen_at": "...", "last_seen_at": "..." },
    { "fingerprint": "xyz...", "label": "Laptop Pribadi", "first_seen_at": "...", "last_seen_at": "..." }
  ],
  "max_devices": 2
}
```

### 3.5 Rate limit

Tidak ada rate-limit eksplisit di endpoint ini, **tetapi** semua endpoint Vibetool di belakang reverse proxy Hostinger yang akan throttle kalau spam (>~60 req/menit/IP). Software cukup validate **1x per startup**.

### 3.6 Device tracking (opsional, tapi sangat disarankan)

Kalau Anda mau **1 lisensi cuma boleh dipakai di N device** (misal 1 PC, atau 2 PC untuk dual-boot), aktifkan device tracking dengan cara:

1. **Admin set `max_devices`** di Vibetool (`/admin/products/{id}/edit` → field "Batas Device per Lisensi"). Default = 1.
2. **Software generate fingerprint** dari hardware/OS info yang stabil di-PC-yang-sama. Lihat contoh di bawah.
3. **Software kirim `device_fingerprint`** (dan optional `device_label`) di setiap request validate.
4. **Server tracking otomatis**:
   - Fingerprint baru + masih ada slot → **register**, return `valid: true` + `device: {...}`.
   - Fingerprint sudah pernah register → **touch** `last_seen_at` + return `valid: true`.
   - Fingerprint baru + slot penuh → return `valid: false, error: device_limit_exceeded` + list device yang sudah terdaftar.
5. **Reset device**: kalau user ganti laptop, admin buka `/admin/licenses/{produk}` → tombol **Reset Device** (hapus semua) atau **Hapus device** (per-device). Setelah reset, user bisa aktivasi ulang di device baru.

#### Cara generate fingerprint yang stabil

Fingerprint harus **stabil di device yang sama** tapi **berbeda di device lain**. Contoh hash dari kombinasi atribut hardware/OS:

**Node.js** (pakai `node:os` + `node:crypto`, no deps):
```javascript
const os = require('node:os');
const crypto = require('node:crypto');

function getDeviceFingerprint() {
  // Ambil MAC address pertama yang non-internal (bukan loopback / virtual)
  const ifaces = os.networkInterfaces();
  let mac = '';
  for (const name of Object.keys(ifaces)) {
    for (const iface of ifaces[name]) {
      if (!iface.internal && iface.mac && iface.mac !== '00:00:00:00:00:00') {
        mac = iface.mac;
        break;
      }
    }
    if (mac) break;
  }
  const raw = [mac, os.hostname(), os.platform(), os.arch(), os.userInfo().username].join('|');
  return crypto.createHash('sha256').update(raw).digest('hex');
}

function getDeviceLabel() {
  return `${os.hostname()} - ${os.platform()} ${os.arch()}`;
}
```

**Python** (pakai `uuid`, `platform`, `hashlib`, `getpass` — stdlib):
```python
import uuid, platform, hashlib, getpass

def get_device_fingerprint() -> str:
    mac = uuid.getnode()  # int representing MAC address
    raw = '|'.join([
        format(mac, 'x'),
        platform.node(),
        platform.system(),
        platform.machine(),
        getpass.getuser(),
    ])
    return hashlib.sha256(raw.encode()).hexdigest()

def get_device_label() -> str:
    return f"{platform.node()} - {platform.system()} {platform.machine()}"
```

**PHP** (mix dari `php_uname` + MAC via `ifconfig` / `getmac`):
```php
function getDeviceFingerprint(): string {
    // MAC address (best-effort)
    $mac = '';
    if (PHP_OS_FAMILY === 'Windows') {
        @exec('getmac', $out);
        foreach ($out as $line) {
            if (preg_match('/([0-9A-F]{2}[-:]){5}[0-9A-F]{2}/i', $line, $m)) { $mac = $m[0]; break; }
        }
    } else {
        @exec('ip link show 2>/dev/null || ifconfig 2>/dev/null', $out);
        foreach ($out as $line) {
            if (preg_match('/(?:HWaddr|ether|link\/ether)\s+([0-9a-f:]{17})/i', $line, $m)) { $mac = $m[1]; break; }
        }
    }
    $raw = implode('|', [$mac, php_uname('n'), php_uname('s'), php_uname('m'), get_current_user()]);
    return hash('sha256', $raw);
}

function getDeviceLabel(): string {
    return php_uname('n') . ' - ' . php_uname('s') . ' ' . php_uname('m');
}
```

**Catatan penting:**
- **Jangan pakai random UUID** — software harus kasih fingerprint **yang sama** tiap startup di PC yang sama, kalau random user akan tertolak setelah restart.
- **Jangan pakai `crypto.randomUUID()` lalu cache** — kalau user clear cache / re-install software, fingerprint akan beda dan jadi "device baru". Pakai info hardware/OS yang persistent.
- Pakai SHA-256 hex (panjang 64) atau truncate kalau perlu — max 128 karakter di server.
- Kalau user pakai VPN atau virtualisasi, MAC bisa berubah — di kasus itu lebih mengandalkan hostname + username untuk stabilitas. Trade-off: kurang unik antar VM.

---

## 3.7 Webhook events (opsional, untuk server software builder)

Vibetool bisa **push notification otomatis** ke endpoint software builder setiap kali ada perubahan status lisensi. Berguna kalau software Anda punya database sendiri yang harus tetap sinkron — mis. auto-aktivasi user, soft-disable account, dll.

**Event yang didukung:**

| Event | Trigger | Frekuensi |
|---|---|---|
| `license.issued` | Lisensi pertama kali di-assign ke user (auto-issue saat order paid, atau admin manual assign) | Sekali per lisensi |
| `license.revoked` | Lisensi di-hapus oleh admin (mis. refund) | Sekali per lisensi |
| `license.renewed` | Admin extend `expires_at` lisensi | Setiap renewal |

### 3.7.1 Setup di Vibetool

Admin produk:
1. Login di `/admin/login`.
2. Buka **Admin → Produk → Edit** produk software Anda.
3. Scroll ke section **Webhook (opsional)**:
   - **Webhook URL** — endpoint software builder (mis. `https://software-anda.com/api/vibetool-webhook`). Harus HTTPS di production.
   - **Webhook Secret** — random string min 32 karakter. Dipakai untuk HMAC-SHA256 signature. Simpan secret yang sama di software builder.
4. Save.

Setelah disimpan, semua event lisensi untuk produk ini akan otomatis di-POST ke webhook URL.

### 3.7.2 Format request yang dikirim Vibetool

**Method:** `POST`
**Content-Type:** `application/json`
**Timeout:** Software builder harus respond dalam **10 detik**, kalau tidak Vibetool tandai gagal.

**Headers:**
```
Content-Type: application/json
User-Agent: Vibetool-Webhook/1.0
X-Vibetool-Event: license.issued
X-Vibetool-Delivery: <UUID — unik per delivery, bisa pakai untuk dedup>
X-Vibetool-Signature: sha256=<hex digest HMAC-SHA256 dari raw body + webhook_secret>
```

**Body (sama untuk semua 3 event):**
```json
{
  "event": "license.issued",
  "occurred_at": "2025-12-25T10:30:00+00:00",
  "license": {
    "key": "ABCD-1234-EFGH-5678",
    "assigned_at": "2025-12-25T10:30:00+00:00",
    "expires_at": "2026-12-25T10:30:00+00:00",
    "is_lifetime": false
  },
  "product": {
    "id": 12,
    "title": "Telegram Blaster Pro",
    "slug": "telegram-blaster-pro"
  },
  "user": {
    "id": 345,
    "name": "Budi Hartono",
    "email": "budi@example.com"
  }
}
```

### 3.7.3 Verifikasi signature (WAJIB)

Tanpa verifikasi siapa saja bisa POST data palsu ke webhook URL Anda. Selalu hitung signature dari raw body + secret, lalu bandingkan dengan header.

**Node.js (Express)**
```javascript
const crypto = require('node:crypto');
const express = require('express');
const app = express();

const WEBHOOK_SECRET = process.env.VIBETOOL_WEBHOOK_SECRET;

// IMPORTANT: gunakan raw body, bukan parsed JSON, supaya signature match.
app.post('/api/vibetool-webhook', express.raw({ type: 'application/json' }), (req, res) => {
  const signature = req.header('X-Vibetool-Signature') || '';
  const expected = 'sha256=' + crypto
    .createHmac('sha256', WEBHOOK_SECRET)
    .update(req.body)
    .digest('hex');

  // timing-safe compare
  if (signature.length !== expected.length ||
      !crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected))) {
    return res.status(401).json({ error: 'invalid signature' });
  }

  const payload = JSON.parse(req.body.toString());
  // ... handle event ...

  res.status(200).json({ ok: true });
});
```

**Python (Flask)**
```python
import hmac, hashlib, os
from flask import Flask, request, abort

app = Flask(__name__)
WEBHOOK_SECRET = os.environ['VIBETOOL_WEBHOOK_SECRET']

@app.post('/api/vibetool-webhook')
def webhook():
    raw = request.get_data()  # raw bytes — BUKAN request.json
    signature = request.headers.get('X-Vibetool-Signature', '')
    expected = 'sha256=' + hmac.new(
        WEBHOOK_SECRET.encode(), raw, hashlib.sha256
    ).hexdigest()
    if not hmac.compare_digest(signature, expected):
        abort(401)
    payload = request.get_json()
    # ... handle event ...
    return {'ok': True}, 200
```

**PHP (plain, tanpa framework)**
```php
<?php
$secret = getenv('VIBETOOL_WEBHOOK_SECRET');
$raw = file_get_contents('php://input');                       // raw bytes
$signature = $_SERVER['HTTP_X_VIBETOOL_SIGNATURE'] ?? '';
$expected = 'sha256=' . hash_hmac('sha256', $raw, $secret);

if (! hash_equals($expected, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid signature']);
    exit;
}
$payload = json_decode($raw, true);
// ... handle event ...
http_response_code(200);
echo json_encode(['ok' => true]);
```

### 3.7.4 Best practice di endpoint Anda

- **Idempotent:** event yang sama bisa kebetulan dikirim 2x (mis. admin retry). Gunakan `X-Vibetool-Delivery` (UUID per delivery) untuk dedup, ATAU minimal cek `license.key` + `event` untuk skip kalau sudah pernah diproses.
- **Respond cepat:** kembalikan `200` segera setelah validasi signature. Lakukan pekerjaan berat (kirim email, panggil API lain) di background job — Vibetool akan tandai gagal kalau request >10 detik.
- **Status code:** `2xx` = sukses. `3xx`/`4xx`/`5xx` = gagal (admin lihat di log delivery).
- **Logging:** simpan setiap delivery (event, payload, response) supaya bisa di-audit kalau ada masalah.

### 3.7.5 Retry & log

Setiap event di-log otomatis di `/admin/produk/{id}/edit → Lihat Riwayat Pengiriman Webhook`:

- Detail status code, response body, signature, dan payload tersimpan.
- Kalau gagal (timeout / non-2xx), admin bisa klik **Retry** untuk kirim ulang manual. Vibetool **tidak** auto-retry — admin yang putuskan kapan retry.
- Setiap retry mencatat attempt # baru di tabel delivery (row asli tidak di-overwrite).

---

## 4. Mode B — Software Gratis (Login Akun Vibetool)

### 4.1 Flow di software

```
[User pasang software]
        │
        ▼
[Software tampilkan form: Email + Password (akun Vibetool)]
        │
        ▼
[Software POST ke /api/auth/validate-member]
        │
        ▼
   ┌────────┴────────────────────────────────┐
   │ valid: true                              │ → simpan token lokal* → buka aplikasi
   │ valid: false, error: invalid_credentials │ → email/password salah
   │ valid: false, error: account_inactive    │ → akun belum diaktifkan admin
   │ valid: false, error: no_access           │ → akun belum klaim produk ini di Vibetool
   │ valid: false, error: product_not_found   │ → slug produk salah (bug software)
   └──────────────────────────────────────────┘
```

> *Software **tidak menyimpan password** lokal. Yang disimpan cukup boolean "sudah login" + cached `user.id` & `user.email`. Re-validate ke server saat startup berikutnya kalau perlu, atau hanya saat user logout/login ulang.

**Penting — perilaku software yang benar:**
- **Jangan simpan password user** di file/registry. Validasi sekali di startup, lalu simpan flag "authorized" + email saja.
- Kalau error `account_inactive` atau `no_access`, tampilkan tombol **"Hubungi Admin"** + tombol **"Klaim di Vibetool"** (link ke landing page).
- Kalau error `invalid_credentials`, beri tombol **"Daftar"** kalau Anda support register flow (Section 4.5).

### 4.2 Endpoint validasi

**`POST /api/auth/validate-member`**

Headers wajib:
```
Content-Type: application/json
Accept: application/json
```

Body (JSON):
```json
{
  "email": "budi@example.com",
  "password": "RahasiaUser123",
  "product_slug": "wa-multi-device-free"
}
```

| Field | Wajib | Tipe | Keterangan |
|---|---|---|---|
| `email` | ya | email | Email akun Vibetool user. |
| `password` | ya | string | Password akun Vibetool user (plain di request body — transport-nya HTTPS). |
| `product_slug` | **ya** | string **atau** array | Slug produk free yang ingin diakses. Boleh kirim array untuk satu software yang menerima beberapa produk free — lihat 3.2.1 (perilaku identik dengan Mode A). |

### 4.3 Response sukses (HTTP 200)

```json
{
  "valid": true,
  "message": "Akses valid.",
  "user": {
    "id": 42,
    "name": "Budi",
    "email": "budi@example.com"
  },
  "product": {
    "id": 7,
    "title": "WA Multi Device Free",
    "slug": "wa-multi-device-free",
    "type": "free"
  },
  "matched_slug": "wa-multi-device-free"
}
```

Tampilkan `user.name` di software seperti pada Mode A. Kalau Anda kirim `product_slug` sebagai array, `matched_slug` memberi tahu produk mana yang dipakai user (dipilih berdasar urutan pertama di array yang user punya akses berbayar).

### 4.4 Response gagal

| HTTP | `error` | Arti | UI yang disarankan |
|---|---|---|---|
| 422 | (validasi Laravel) | Email/password/slug kosong / format salah | Tampilkan ulang form. |
| 404 | `product_not_found` | Slug salah / produk dinonaktifkan admin | **Bug di software** — log untuk developer. |
| 401 | `invalid_credentials` | Email/password salah | "Email atau password salah." + tombol Daftar. |
| 403 | `account_inactive` | Akun masih `pending` (belum diaktifkan admin) | "Akun belum diaktifkan. Hubungi admin." |
| 403 | `no_access` | Akun valid, tapi belum klaim produk gratis ini | "Silakan klaim produk dulu di Vibetool" + link `https://vibetool.id/p/{slug}` |

### 4.5 (Opsional) Daftar member baru langsung dari software

Kalau Anda ingin user bisa **register tanpa keluar dari software**:

**`POST /api/auth/register`**

Rate limit: **10 request/menit/IP**.

Body (JSON):
```json
{
  "name": "Budi Tester",
  "email": "budi@example.com",
  "whatsapp_number": "081234567890",
  "password": "Rahasia123!",
  "password_confirmation": "Rahasia123!"
}
```

| Field | Wajib | Keterangan |
|---|---|---|
| `name` | ya | Nama lengkap. |
| `email` | ya | Email valid, unik. |
| `whatsapp_number` | tidak (tapi sangat disarankan) | Format bebas (08xx / +62xx / 8xx) — server normalize ke `62xxxxxxxxxx`. Wajib kalau Anda mau admin bisa hubungi user via WA untuk aktivasi. |
| `password` | ya | Min 8 karakter (Laravel default). |
| `password_confirmation` | ya | Harus sama dengan `password`. |

Response sukses (HTTP 201):
```json
{
  "ok": true,
  "status": "pending",
  "message": "Registrasi berhasil. Akun menunggu aktivasi admin via WhatsApp.",
  "user": {
    "id": 99,
    "name": "Budi Tester",
    "email": "budi@example.com",
    "whatsapp_number": "6281234567890"
  }
}
```

**Akun baru ber-status `pending`** — admin harus aktifkan dulu lewat dashboard Vibetool sebelum user bisa login. Software harus tampilkan instruksi:

> *"Akun berhasil dibuat. Admin akan menghubungi Anda lewat WhatsApp untuk aktivasi. Setelah aktif, login dengan email & password yang sudah Anda buat."*

Response gagal validasi (HTTP 422):
```json
{
  "ok": false,
  "error": "validation_error",
  "message": "Data tidak valid. Periksa kembali isian.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field confirmation does not match."]
  }
}
```

Iterate `errors` (object dengan key = field, value = array of pesan) untuk tampilkan inline error per field.

---
## 5. Tombol "Hubungi Admin"

Ambil nomor WA admin dari endpoint publik supaya selalu up-to-date kalau admin ganti nomor:

**`GET /api/setting/whatsapp-admin`**

Rate limit: 60 req/menit/IP.

Response sukses:
```json
{ "number": "6282312181216" }
```

Response gagal (admin belum set):
```json
{ "number": null, "message": "Nomor WhatsApp admin belum dikonfigurasi." }
```

**Cara pakai di software** — generate link `https://wa.me/{number}?text=...`:
```
https://wa.me/6282312181216?text=Halo%20admin%2C%20saya%20butuh%20bantuan%20software%20{nama_software}
```

Buka link ini di browser default user — WA Web / WA Desktop / WA HP akan langsung membuka chat ke admin.

---

## 6. Contoh kode (3 bahasa populer)

### 6.1 Node.js (`fetch`, built-in di Node 18+)

```javascript
// licenseClient.js
const os = require('node:os');
const crypto = require('node:crypto');

const BASE_URL = 'https://vibetool.id/api';
const PRODUCT_SLUG = 'telegram-blaster-pro'; // hardcode slug produk Anda

function getDeviceFingerprint() {
  const ifaces = os.networkInterfaces();
  let mac = '';
  for (const name of Object.keys(ifaces)) {
    for (const iface of ifaces[name]) {
      if (!iface.internal && iface.mac && iface.mac !== '00:00:00:00:00:00') { mac = iface.mac; break; }
    }
    if (mac) break;
  }
  const raw = [mac, os.hostname(), os.platform(), os.arch(), os.userInfo().username].join('|');
  return crypto.createHash('sha256').update(raw).digest('hex');
}

function getDeviceLabel() {
  return `${os.hostname()} - ${os.platform()} ${os.arch()}`;
}

async function validateLicense(key) {
  const res = await fetch(`${BASE_URL}/license/validate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      key,
      product_slug: PRODUCT_SLUG,
      device_fingerprint: getDeviceFingerprint(),
      device_label: getDeviceLabel(),
    }),
  });
  const data = await res.json();
  return { ok: res.ok, ...data };
}

async function validateMember(email, password) {
  const res = await fetch(`${BASE_URL}/auth/validate-member`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ email, password, product_slug: PRODUCT_SLUG }),
  });
  const data = await res.json();
  return { ok: res.ok, ...data };
}

async function getAdminWhatsapp() {
  const res = await fetch(`${BASE_URL}/setting/whatsapp-admin`);
  const data = await res.json();
  return data.number; // string atau null
}

// Pemakaian:
(async () => {
  const result = await validateLicense('ABCD-1234-EFGH-5678');
  if (result.valid) {
    console.log(`Login OK, user: ${result.license.user.name}`);
  } else {
    console.error(`Error: ${result.message}`);
  }
})();
```

### 6.2 Python (`requests`)

```python
import requests

BASE_URL = 'https://vibetool.id/api'
PRODUCT_SLUG = 'telegram-blaster-pro'

def _device_fingerprint() -> str:
    import uuid, platform, hashlib, getpass
    raw = '|'.join([
        format(uuid.getnode(), 'x'),
        platform.node(),
        platform.system(),
        platform.machine(),
        getpass.getuser(),
    ])
    return hashlib.sha256(raw.encode()).hexdigest()

def _device_label() -> str:
    import platform
    return f"{platform.node()} - {platform.system()} {platform.machine()}"

def validate_license(key: str) -> dict:
    r = requests.post(
        f'{BASE_URL}/license/validate',
        json={
            'key': key,
            'product_slug': PRODUCT_SLUG,
            'device_fingerprint': _device_fingerprint(),
            'device_label': _device_label(),
        },
        headers={'Accept': 'application/json'},
        timeout=10,
    )
    return r.json()

def validate_member(email: str, password: str) -> dict:
    r = requests.post(
        f'{BASE_URL}/auth/validate-member',
        json={'email': email, 'password': password, 'product_slug': PRODUCT_SLUG},
        headers={'Accept': 'application/json'},
        timeout=10,
    )
    return r.json()

def get_admin_whatsapp() -> str | None:
    r = requests.get(f'{BASE_URL}/setting/whatsapp-admin', timeout=10)
    return r.json().get('number')

# Pemakaian:
result = validate_license('ABCD-1234-EFGH-5678')
if result.get('valid'):
    print(f"Login OK, user: {result['license']['user']['name']}")
else:
    print(f"Error: {result.get('message')}")
```

### 6.3 PHP (cURL, plain — tidak butuh Laravel/framework)

```php
<?php

const BASE_URL = 'https://vibetool.id/api';
const PRODUCT_SLUG = 'telegram-blaster-pro';

function vibetoolPost(string $path, array $payload): array
{
    $ch = curl_init(BASE_URL . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body, true) ?? [];
}

function vibetoolDeviceFingerprint(): string {
    $mac = '';
    if (PHP_OS_FAMILY === 'Windows') {
        @exec('getmac', $out);
        foreach ($out as $line) {
            if (preg_match('/([0-9A-F]{2}[-:]){5}[0-9A-F]{2}/i', $line, $m)) { $mac = $m[0]; break; }
        }
    } else {
        @exec('ip link show 2>/dev/null || ifconfig 2>/dev/null', $out);
        foreach ($out as $line) {
            if (preg_match('/(?:HWaddr|ether|link\/ether)\s+([0-9a-f:]{17})/i', $line, $m)) { $mac = $m[1]; break; }
        }
    }
    $raw = implode('|', [$mac, php_uname('n'), php_uname('s'), php_uname('m'), get_current_user()]);
    return hash('sha256', $raw);
}

function validateLicense(string $key): array
{
    return vibetoolPost('/license/validate', [
        'key' => $key,
        'product_slug' => PRODUCT_SLUG,
        'device_fingerprint' => vibetoolDeviceFingerprint(),
        'device_label' => php_uname('n') . ' - ' . php_uname('s'),
    ]);
}

function validateMember(string $email, string $password): array
{
    return vibetoolPost('/auth/validate-member', [
        'email' => $email,
        'password' => $password,
        'product_slug' => PRODUCT_SLUG,
    ]);
}

// Pemakaian:
$result = validateLicense('ABCD-1234-EFGH-5678');
if ($result['valid'] ?? false) {
    echo "Login OK, user: {$result['license']['user']['name']}\n";
} else {
    echo "Error: {$result['message']}\n";
}
```

---

## 7. Checklist integrasi (untuk developer / AI integrator)

Tandai satu per satu:

**Untuk SEMUA software:**
- [ ] Sudah dapat `product_slug` dari admin Vibetool / dari URL landing page.
- [ ] Hardcode `BASE_URL = "https://vibetool.id/api"` di config software.
- [ ] Hardcode `PRODUCT_SLUG` di config software.
- [ ] Pakai HTTPS (jangan `http://`), karena password / license dikirim plain.
- [ ] Set timeout HTTP request ~10 detik supaya UI tidak gantung saat network lambat.
- [ ] Tampilkan pesan error dari field `message` apa adanya — sudah bahasa Indonesia user-friendly.
- [ ] Tambah tombol **"Hubungi Admin"** yang fetch nomor WA dari `/setting/whatsapp-admin` lalu buka `wa.me/{number}?text=...`.

**Khusus software BERBAYAR (Mode A):**
- [ ] Form input lisensi muncul saat first run.
- [ ] POST ke `/license/validate` dengan `{ key, product_slug, device_fingerprint, device_label }`.
- [ ] Fingerprint device stabil di device yang sama (lihat Section 3.6 — hash dari MAC + hostname + OS + username, BUKAN random UUID).
- [ ] Cache hasil sukses di file lokal (key only, atau key + cached timestamp).
- [ ] Re-validate maksimal 1x per startup.
- [ ] Handle `license_not_found` (key salah), `license_expired` (perpanjangan), dan `device_limit_exceeded` (tampilkan list device terdaftar + tombol Hubungi Admin).

**Khusus software GRATIS (Mode B):**
- [ ] Form login (email + password) muncul saat first run.
- [ ] POST ke `/auth/validate-member` dengan `{ email, password, product_slug }`.
- [ ] **JANGAN simpan password lokal** — cukup flag "authorized" + `user.id`/`email`.
- [ ] Handle `invalid_credentials` (form ulang), `account_inactive` (hubungi admin), `no_access` (link ke landing page klaim).
- [ ] (Opsional) Form registrasi yang POST ke `/auth/register` + instruksi tunggu aktivasi admin.

**Sebelum rilis:**
- [ ] Test lengkap dengan key/akun real di staging Vibetool (request admin buatkan dummy).
- [ ] Test semua skenario gagal (key salah, key expired, akun pending, akun belum klaim).
- [ ] Test offline behavior (graceful degradation, bukan crash).

---

## 8. FAQ

**Q: Software saya butuh limit jumlah device per lisensi (mis. 1 lisensi = 1 PC). Bagaimana?**
A: Saat ini API hanya memvalidasi key & expiry, **tidak** ada device tracking. Implementasi device-binding di sisi software:
- Generate device fingerprint (hash MAC + hostname + dll).
- Saat user pertama kali aktivasi, simpan fingerprint di file lokal **dan** di server software Anda sendiri (kalau ada).
- Saat startup, cek fingerprint match.
- Untuk multi-device tracking server-side, butuh fitur baru di Vibetool — request ke admin.

**Q: Bagaimana cara cancel/revoke lisensi user yang refund?**
A: Admin Vibetool akan ubah `order` status ke non-`paid` (atau hapus `order_id` dari `license`) → endpoint `/license/validate` otomatis return `license_not_found`. Software cukup re-validate periodically.

**Q: Apakah ada webhook ketika lisensi baru di-issue?**
A: Belum. Saat ini polling-based. Kalau perlu webhook, request ke admin.

**Q: Endpoint butuh API key / authentication?**
A: Tidak. Semua endpoint di Section 2 publik (rate-limited di sisi server). Validasi terjadi via license key (Mode A) atau email+password (Mode B) — itu sendiri sudah berfungsi sebagai credential.

**Q: User saya pakai WhatsApp tapi nomornya bukan Indonesia. Apakah `whatsapp_number` di register support?**
A: Helper normalisasi saat ini optimized untuk nomor Indonesia (62). Format internasional lain (`+1...`, `+44...`) di-strip non-digit dan disimpan apa adanya — bisa, tapi format display di dashboard admin mungkin kurang rapi. Untuk produk yang target user internasional, isi `whatsapp_number` opsional / kosong.

**Q: Kalau Vibetool down / network error, apakah software bisa tetap jalan?**
A: Itu keputusan Anda sebagai builder software. Saran: simpan timestamp validasi terakhir, izinkan offline grace period ~7 hari, tampilkan warning di UI kalau cache > 1 hari.

**Q: Bisakah saya test endpoint tanpa akun real?**
A: Ya — endpoint produktif menerima request siapa pun, tinggal pakai key/email/password yang akan return error (`license_not_found` / `invalid_credentials`). Untuk happy path, request admin buatkan dummy akun + lisensi di environment staging.

---

## 9. Untuk AI assistant yang mengintegrasi software ini

Kalau Anda AI (Devin / lainnya) yang ditugaskan integrasi software ke Vibetool, ikuti langkah ini:

1. **Tanya developer / user**:
   - Software ini tipe **berbayar** (license key) atau **gratis** (login akun Vibetool)?
   - Apa `product_slug`-nya? (Cek URL landing page di Vibetool: `https://vibetool.id/p/{slug}`)
   - Bahasa pemrograman / framework software-nya apa? (Node.js / Python / PHP / lainnya)
2. **Pilih Section 3 (Mode A) atau Section 4 (Mode B)** sesuai jawaban.
3. **Copy snippet code** dari Section 6 sesuai bahasa software, ubah `PRODUCT_SLUG`.
4. **Jalankan checklist Section 7** untuk semua item — jangan skip.
5. **Test live** dengan request dummy key/akun ke admin Vibetool sebelum rilis.

Jangan tambah lapisan baru (mis. JWT signing, OAuth flow, dll) — endpoint sudah final dan stateless. Tambahan akan menambah complexity tanpa benefit.

Kalau ada kebutuhan endpoint baru (mis. webhook, list lisensi user, device tracking) — **jangan modifikasi software untuk hack workaround**. Open issue / request ke admin Vibetool dulu.

---

**Document version:** 1.0
**Last updated:** 2026-05-30
**Maintainer:** Admin Vibetool (`admin@vibetool.id`)
