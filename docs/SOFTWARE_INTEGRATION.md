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
  "product_slug": "telegram-blaster-pro"
}
```

| Field | Wajib | Tipe | Keterangan |
|---|---|---|---|
| `key` | ya | string | Kode lisensi yang di-paste user. |
| `product_slug` | tidak (tapi **disarankan**) | string | Slug produk dari Vibetool. Jika diisi, server pastikan lisensi memang untuk produk ini (cegah user pakai key produk lain). |

**Slug produk** = bagian terakhir URL landing page produk di Vibetool, misal: `https://vibetool.id/p/telegram-blaster-pro` → slug = `telegram-blaster-pro`. **Hardcode** slug ini di software Anda.

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
      "slug": "telegram-blaster-pro"
    },
    "user": {
      "id": 42,
      "name": "Budi",
      "email": "budi@example.com"
    },
    "assigned_at": "2026-05-20T10:23:00+00:00",
    "expires_at": "2027-05-20T10:23:00+00:00",
    "is_lifetime": false
  }
}
```

| Field penting | Cara pakai |
|---|---|
| `license.user.name` / `email` | Tampilkan di pojok kanan atas software ("Halo, Budi"). |
| `license.expires_at` | Tampilkan tanggal kedaluwarsa di About / Settings software. `null` artinya lifetime. |
| `license.is_lifetime` | `true` = lisetime, jangan tampilkan expiry. |

### 3.4 Response gagal

| HTTP | `error` | Kapan muncul | Yang harus software lakukan |
|---|---|---|---|
| 422 | (validasi Laravel) | `key` kosong | Minta user paste key. |
| 404 | `license_not_found` | Key salah / belum dialokasikan / bukan untuk produk ini | Tampilkan pesan + minta user copy ulang dari email/dashboard Vibetool. |
| 403 | `license_expired` | Lisensi sudah lewat tanggal kedaluwarsa | Tampilkan pesan + ajak user perpanjang (link ke landing page produk). |

Contoh response gagal:
```json
{
  "valid": false,
  "error": "license_not_found",
  "message": "Kunci lisensi tidak ditemukan atau belum dialokasikan."
}
```

### 3.5 Rate limit

Tidak ada rate-limit eksplisit di endpoint ini, **tetapi** semua endpoint Vibetool di belakang reverse proxy Hostinger yang akan throttle kalau spam (>~60 req/menit/IP). Software cukup validate **1x per startup**.

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
| `product_slug` | **ya** | string | Slug produk free yang ingin diakses. **Hardcode** di software. |

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
  }
}
```

Tampilkan `user.name` di software seperti pada Mode A.

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
const BASE_URL = 'https://vibetool.id/api';
const PRODUCT_SLUG = 'telegram-blaster-pro'; // hardcode slug produk Anda

async function validateLicense(key) {
  const res = await fetch(`${BASE_URL}/license/validate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ key, product_slug: PRODUCT_SLUG }),
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

def validate_license(key: str) -> dict:
    r = requests.post(
        f'{BASE_URL}/license/validate',
        json={'key': key, 'product_slug': PRODUCT_SLUG},
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

function validateLicense(string $key): array
{
    return vibetoolPost('/license/validate', ['key' => $key, 'product_slug' => PRODUCT_SLUG]);
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
- [ ] POST ke `/license/validate` dengan `{ key, product_slug }`.
- [ ] Cache hasil sukses di file lokal (key only, atau key + cached timestamp).
- [ ] Re-validate maksimal 1x per startup.
- [ ] Handle `license_not_found` (key salah) dan `license_expired` (perpanjangan).

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
