# Panduan Akses Produk Gratis (Free Product)

Dokumen ini menjelaskan cara kerja **Produk Gratis** di Vibetool/PRODIG — tipe produk yang dibagikan tanpa biaya. Member tinggal klaim (tanpa checkout/pembayaran), dan untuk produk gratis yang berupa software, validasi akses di sisi software dilakukan menggunakan **email + password akun member PRODIG**, bukan license key.

> Fitur ini melengkapi sistem lisensi yang sudah ada (lihat [`LICENSE_ACCESS.md`](LICENSE_ACCESS.md)). Produk berbayar tipe `software` tetap pakai license key; produk tipe `free` pakai validasi akun member.

---

## 1. Konsep Singkat

| Istilah | Penjelasan |
|---|---|
| **Free Product** | Produk dengan `product_type = free`. Harga otomatis `0`, klaim tanpa checkout. |
| **Klaim** | Aksi member untuk "membeli" produk gratis. Membentuk `Order` dengan `amount=0` dan `status=paid`. |
| **Member Auth Validation** | Cara software klien memverifikasi akses user: kirim `email + password + product_slug` ke API, server balas `valid: true/false`. |
| **Payment Method `free`** | Nilai baru di kolom `orders.payment_method` untuk membedakan order klaim gratis dari `manual` / `xendit`. |

### Perbedaan vs Produk Berbayar (Software)

| Aspek | Software berbayar | Produk Gratis (software) |
|---|---|---|
| Harga | > 0 | 0 (paksa di server) |
| Alur akuisisi | Checkout → bayar → konfirmasi → order paid | Klaim 1-klik → order langsung paid |
| Kredensial akses | License key (`XXXX-XXXX-XXXX-XXXX`) | Email + password akun PRODIG |
| Validasi di software | `POST /api/license/validate` | `POST /api/auth/validate-member` |
| Lisensi di tabel `licenses` | Ada, satu baris per order | **Tidak ada** — tidak generate baris lisensi |
| `payment_method` | `manual` / `xendit` | `free` |
| Komisi affiliator/upline | Dihitung dari amount | `0` karena amount=0 (tracking tetap jalan) |

---

## 2. Skema Database

Tidak ada migration baru. Fitur ini pakai value baru di kolom yang sudah ada:

| Tabel | Kolom | Value baru |
|---|---|---|
| `products` | `product_type` | `'free'` (di samping `'digital'`, `'software'`) |
| `orders` | `payment_method` | `'free'` (di samping `'manual'`, `'xendit'`) |
| `orders` | `amount` | `0` (untuk semua order produk free) |
| `orders` | `status` | `'paid'` langsung saat klaim |

Kolom `products.license_duration`, tabel `licenses`, dan kolom `orders.payment_proof` **tidak digunakan** untuk produk free.

---

## 3. Alur untuk Admin

### 3.1 Membuat Produk Gratis

1. Buka **Admin Panel → Products → Tambah Produk** (`/admin/products/create`).
2. Pada **Tipe Produk**, pilih **Produk Gratis (Software via login akun)**.
3. Form akan menyembunyikan input **Harga** dan menampilkan notice hijau **"Harga: GRATIS"**.
4. Input **Masa Berlaku Lisensi** juga tersembunyi (tidak relevan).
5. Lengkapi data lainnya:
   - **File / URL produk** (opsional) — installer atau link download
   - **Thumbnail / hero image**
   - **Landing page** (opsional)
6. Simpan. Server otomatis set `price = 0` (input price diabaikan kalau type = `free`).

### 3.2 Tampilan di List Admin

Di `/admin/products`, produk free ditandai dengan badge hijau **"Produk Gratis"** dan harga ditampilkan sebagai **GRATIS** (warna hijau `#10b981`).

### 3.3 Edit Produk Existing

Mengubah tipe produk dari `digital`/`software` ke `free` **diperbolehkan**, tapi hati-hati:
- Order lama dengan amount > 0 tetap historis (tidak diubah).
- Order baru langsung pakai amount=0.
- Member yang sudah beli versi berbayar tetap punya akses (Order paid history tetap valid).

---

## 4. Alur untuk Member (Pembeli)

### 4.1 Klaim dari Dashboard

Halaman: **Dashboard → Produk** (`/dashboard/products`)

Produk free tampil dengan:
- Harga: **GRATIS** (hijau)
- Catatan: *"Klaim langsung — login software pakai email + password akun PRODIG kamu."*
- Tombol hijau **"Dapatkan Gratis"** (form `POST /free/{slug}/claim` dengan CSRF)

Setelah klik, member langsung diarahkan ke `/dashboard/purchases` dengan flash sukses.

### 4.2 Klaim dari Landing Page

Halaman: `/p/{slug}` (public — `product/landing.blade.php` atau `product/show.blade.php`)

- Untuk free product, harga ditampilkan **GRATIS** dan tombol berubah jadi **"Dapatkan Gratis"** (hijau).
- Jika **belum login**, tombol mengarah ke `/register` (sambil simpan `ref` cookie kalau ada).
- Jika **sudah login**, tombol POST ke `/free/{slug}/claim`.

### 4.3 Tampilan Setelah Klaim

Halaman: **Dashboard → Pembelian Saya** (`/dashboard/purchases`)

Untuk free product yang sudah diklaim, kartunya menampilkan:
- Badge harga **GRATIS** (hijau)
- Section **"Cara Akses Software"** dengan instruksi:
  > Login ke software ini menggunakan:
  > **Email:** `<email member>`
  > **Password:** password akun PRODIG kamu
- Tombol **Download Produk** / **Buka Link Produk** (kalau ada `file_path` / `file_url`)
- Tombol **Detail Produk**
- Tombol "Lihat Lisensi" **tidak muncul** untuk free product (tidak ada license key).

---

## 5. Alur Backend Klaim (`POST /free/{slug}/claim`)

Controller: `App\Http\Controllers\FreeProductController::claim()`

Middleware: `auth` + `active` (member harus login & akun aktif).

Langkah:

1. Resolve `Product` dari slug. Jika `product_type !== 'free'`, redirect ke `/checkout/{slug}` (defensive).
2. Cek apakah user sudah punya order paid untuk produk ini — kalau iya, redirect ke `/dashboard/purchases` dengan flash info.
3. Resolve `affiliate_id` & `upline_id` dari cookie `ref` / session — sama dengan logika di `CheckoutController`.
4. Insert `Order` baru:
   ```php
   Order::create([
       'user_id' => $user->id,
       'product_id' => $product->id,
       'affiliate_id' => $affiliateId,
       'upline_id' => $uplineId,
       'amount' => 0,
       'status' => 'pending',
       'payment_method' => 'free',
       'download_token' => Str::uuid()->toString(),
   ]);
   ```
5. Panggil `OrderPaymentService::markAsPaid($order)` — ini akan:
   - Update `status = paid`
   - Hitung komisi (semuanya 0 karena `amount=0`)
   - Skip `assignLicense()` karena `product_type !== 'software'`
6. Clear session terkait checkout (`auto_coupon`, `intended_product_slug`, `ref_code`).
7. Trigger `TelegramService::notifyFreeClaim($order)` — kirim notifikasi ke admin Telegram (tanpa tombol "Tandai Lunas" karena sudah otomatis paid).
8. Redirect ke `/dashboard/purchases` dengan flash success.

> Order amount=0 statusnya tetap `paid` (bukan `free`) supaya kompatibel dengan query/relation existing yang sudah filter `status = paid`.

---

## 6. Validasi Akses dari Software Pihak Pembeli

Endpoint publik untuk validasi kredensial member:

```
POST /api/auth/validate-member
Content-Type: application/json
```

Middleware: `throttle:30,1` — maksimum **30 request per menit per IP** (anti brute-force password).

### Request Body

| Field | Tipe | Wajib | Deskripsi |
|---|---|---|---|
| `email` | string (email) | ya | Email login member di PRODIG. |
| `password` | string | ya | Password login member di PRODIG (cleartext, di-hash check di server). |
| `product_slug` | string | ya | Slug produk free yang ingin diakses. Server akan verifikasi member punya order paid untuk produk ini. |

### Contoh

```bash
curl -X POST https://<host>/api/auth/validate-member \
  -H "Content-Type: application/json" \
  -d '{
    "email": "member@example.com",
    "password": "rahasia123",
    "product_slug": "free-tool-pro"
  }'
```

### Response

**200 OK — Valid**

```json
{
  "valid": true,
  "message": "Akses valid.",
  "user": {
    "id": 42,
    "name": "Budi",
    "email": "member@example.com"
  },
  "product": {
    "id": 5,
    "title": "Free Tool Pro",
    "slug": "free-tool-pro",
    "type": "free"
  }
}
```

**404 Not Found — Produk tidak ditemukan / inactive**

```json
{
  "valid": false,
  "error": "product_not_found",
  "message": "Produk tidak ditemukan atau sudah tidak aktif."
}
```

**401 Unauthorized — Email/password salah**

```json
{
  "valid": false,
  "error": "invalid_credentials",
  "message": "Email atau password salah."
}
```

**403 Forbidden — Akun belum aktif**

```json
{
  "valid": false,
  "error": "account_inactive",
  "message": "Akun belum diaktifkan oleh admin."
}
```

**403 Forbidden — Akun belum klaim produk ini**

```json
{
  "valid": false,
  "error": "no_access",
  "message": "Akun ini belum klaim produk gratis ini. Silakan klaim dulu di dashboard PRODIG."
}
```

### Catatan Implementasi

- Endpoint **tidak memerlukan API key/token tambahan** — autentikasi langsung lewat email+password user. Aman dipanggil dari aplikasi software, asal selalu via HTTPS.
- Validasi cek dua hal: (1) kredensial benar (lewat `Hash::check`), (2) user punya `Order` dengan `status=paid` untuk `product_id` tsb (boleh tipe `free` atau `software` — order paid manapun dianggap punya akses).
- Endpoint pakai key `valid` (bukan `success`) untuk konsistensi dengan `/api/license/validate` yang sudah ada.
- Rate limit aktif: 30 req/menit/IP. Setelah limit terlampaui, Laravel response `429 Too Many Requests` dengan header `Retry-After`.

---

## 7. Contoh Integrasi di Software Klien

Berikut contoh sederhana untuk berbagai stack klien.

### 7.1 JavaScript / Electron / Node.js

```javascript
async function validateAccess(email, password, productSlug) {
  const res = await fetch('https://prodig.com/api/auth/validate-member', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password, product_slug: productSlug }),
  });
  const data = await res.json();
  if (!data.valid) {
    throw new Error(data.message || 'Akses tidak valid');
  }
  return { user: data.user, product: data.product };
}

// Penggunaan
try {
  const { user } = await validateAccess(
    document.getElementById('email').value,
    document.getElementById('password').value,
    'free-tool-pro'
  );
  // Simpan token lokal (mis. sessionStorage) supaya tidak validasi ulang setiap aksi
  sessionStorage.setItem('prodig_user_id', user.id);
  startApp(user);
} catch (err) {
  alert(err.message);
}
```

### 7.2 Python

```python
import requests

def validate_access(email: str, password: str, product_slug: str) -> dict:
    res = requests.post(
        "https://prodig.com/api/auth/validate-member",
        json={"email": email, "password": password, "product_slug": product_slug},
        timeout=10,
    )
    data = res.json()
    if not data.get("valid"):
        raise PermissionError(data.get("message", "Akses tidak valid"))
    return data
```

### 7.3 C# / .NET (Desktop App)

```csharp
using System.Net.Http;
using System.Text.Json;

public class ValidateRequest
{
    public string email { get; set; }
    public string password { get; set; }
    public string product_slug { get; set; }
}

public async Task<bool> ValidateAccess(string email, string password, string slug)
{
    var client = new HttpClient();
    var payload = JsonSerializer.Serialize(new ValidateRequest
    {
        email = email, password = password, product_slug = slug,
    });
    var content = new StringContent(payload, Encoding.UTF8, "application/json");
    var res = await client.PostAsync("https://prodig.com/api/auth/validate-member", content);
    var body = JsonSerializer.Deserialize<JsonElement>(await res.Content.ReadAsStringAsync());
    return body.GetProperty("valid").GetBoolean();
}
```

### 7.4 Saran Caching di Klien

Validasi tidak perlu dilakukan setiap user buka software — terlalu banyak request. Saran pola:

1. Validasi sekali saat **login pertama** — simpan `user_id` + `validated_at` di file/registry lokal.
2. Re-validate setiap **24 jam** (atau saat user buka software di hari baru).
3. Kalau API tidak bisa dihubungi (offline), pakai cache lokal dengan TTL singkat (mis. 7 hari) supaya user yang sedang tidak ada internet masih bisa pakai software.
4. Kalau dapat response `no_access`, hapus cache lokal dan paksa user logout.

---

## 8. Tracking Affiliate untuk Produk Free

Klaim free product **tetap tracking** affiliate/upline (lewat cookie `ref` atau `upline_id` user). Hal ini penting supaya:

- Order tercatat di tabel `commissions` dengan referensi ke `affiliate_id` & `upline_id`.
- Statistik downline di dashboard affiliator tetap akurat (sekarang downlinenya klaim produk free → terlihat sebagai aktivitas).
- Bila member kemudian beli produk berbayar lain, attribution affiliate tetap konsisten.

Karena `amount = 0`, perhitungan komisi otomatis menghasilkan `0` di semua level — tidak ada uang yang ditransfer ke affiliator/upline dari klaim free. Ini desain sengaja: free product berfungsi sebagai **lead magnet**, bukan sumber komisi.

---

## 9. Notifikasi Telegram

Saat ada klaim free, `TelegramService::notifyFreeClaim($order)` dipanggil. Format pesan:

```
🎁 Klaim Produk Gratis #<order_id>

Produk: <title>
Member: <name>
Email: <email>
WhatsApp: <whatsapp_number>
Afiliator: <affiliate_name>  (kalau ada)

✅ Akses otomatis diberikan (login via akun member)
```

Tidak ada tombol inline (mis. "Tandai Lunas") karena order sudah otomatis `paid`. Bedakan dari `notifyNewOrder()` yang dipakai untuk order pending (manual transfer atau Xendit).

---

## 10. Ringkasan Endpoint

| Endpoint | Method | Middleware | Deskripsi |
|---|---|---|---|
| `/free/{slug}/claim` | POST | `auth`, `active` | Klaim produk gratis untuk user yang login. |
| `/api/auth/validate-member` | POST | `throttle:30,1` | Validasi kredensial member untuk akses software (publik, rate-limited). |

Endpoint terkait lainnya (sudah ada sebelum fitur ini):

| Endpoint | Method | Deskripsi |
|---|---|---|
| `/api/license/validate` | POST | Validasi license key untuk produk software berbayar (lihat `LICENSE_ACCESS.md`). |
| `/dashboard/products` | GET | List produk untuk member — sekarang menampilkan tombol "Dapatkan Gratis" untuk produk free. |
| `/dashboard/purchases` | GET | Riwayat pembelian + akses produk — sekarang menampilkan section "Cara Akses Software" untuk free product. |
| `/p/{slug}` | GET | Landing page publik produk — sekarang menampilkan harga GRATIS dan tombol klaim untuk produk free. |

---

## 11. File Kunci

Backend:

| File | Peran |
|---|---|
| `app/Models/Product.php` | Helper `isFree()`, `requiresPayment()`. |
| `app/Http/Controllers/Admin/ProductController.php` | Validasi & save form dengan tipe `free`. |
| `app/Http/Controllers/FreeProductController.php` | Endpoint klaim `/free/{slug}/claim`. |
| `app/Http/Controllers/Api/MemberAuthValidationController.php` | Endpoint API `/api/auth/validate-member`. |
| `app/Services/OrderPaymentService.php` | `markAsPaid()` — sudah skip `assignLicense()` untuk non-software. |
| `app/Services/TelegramService.php` | `notifyFreeClaim()` — notifikasi khusus klaim free. |

Frontend (Blade):

| File | Peran |
|---|---|
| `resources/views/admin/products/create.blade.php` | Form admin — dropdown tipe + JS toggle harga. |
| `resources/views/admin/products/edit.blade.php` | Form edit — sama dengan create. |
| `resources/views/admin/products/index.blade.php` | List admin — badge "Produk Gratis" + harga GRATIS. |
| `resources/views/dashboard/products.blade.php` | List member — tombol "Dapatkan Gratis". |
| `resources/views/dashboard/purchases.blade.php` | List akses — section "Cara Akses Software". |
| `resources/views/product/landing.blade.php` | Landing page yang dipublish — CTA GRATIS. |
| `resources/views/product/show.blade.php` | Landing page sederhana (kalau landing belum dipublish) — CTA GRATIS. |
| `resources/views/home.blade.php` | Homepage — kartu produk pakai label GRATIS. |

Routes:

| File | Route |
|---|---|
| `routes/web.php` | `Route::post('/free/{slug}/claim', ...)` (group `auth` + `active`). |
| `routes/api.php` | `Route::post('/auth/validate-member', ...)->middleware('throttle:30,1')`. |

---

## 12. Troubleshooting

| Gejala | Kemungkinan Penyebab | Solusi |
|---|---|---|
| Klik "Dapatkan Gratis" → redirect ke checkout berbayar | `product_type` belum di-set ke `free` di admin | Edit produk di admin, ganti tipe ke "Produk Gratis". |
| Setelah klaim, produk tidak muncul di "Pembelian Saya" | Status order belum `paid` (mis. `markAsPaid()` gagal silent) | Cek log Laravel, lihat error di `OrderPaymentService`. Bisa juga karena query relation `Order::paid()` scope. |
| API `/api/auth/validate-member` return `no_access` walau sudah klaim | Order ada tapi status bukan `paid`, atau `product_slug` salah | Cek tabel `orders` untuk user tsb. Pastikan slug yang dikirim dari klien benar. |
| API return `429 Too Many Requests` | Rate limit 30 req/menit terlampaui | Tunggu 1 menit, atau debounce request di klien. |
| Member belum verifikasi WhatsApp tapi sudah bisa klaim | Memang sengaja — free product tidak butuh WhatsApp aktivasi | Kalau perlu, tambah middleware tambahan di route `/free/{slug}/claim`. |
| Komisi affiliator muncul `0` di dashboard | Memang sengaja — free product menghasilkan amount=0, komisi=0 | Bukan bug. Lihat section 8. |

---

## 13. Referensi Cepat untuk Devin/Engineer Lain

Kalau kamu (Devin lain atau engineer manusia) perlu menambah/modifikasi fitur free product:

1. **Cek dulu tipe produk**: `if ($product->isFree()) { ... }` — jangan hardcode `product_type === 'free'`.
2. **Jangan generate license**: `OrderPaymentService::assignLicense()` sudah filter `product_type === 'software'`. Kalau bikin handler order baru, jangan langsung generate license untuk non-software.
3. **Free claim harus pakai `markAsPaid()`**: jangan langsung set `status=paid` lewat update biasa — `markAsPaid()` handle commission processing yang penting untuk konsistensi data.
4. **Validasi password member**: pakai `Hash::check($plaintext, $user->password)`, JANGAN bandingkan dengan `==` (timing attack).
5. **Rate limiting wajib**: setiap endpoint yang verify password harus di-throttle. Pola yang sudah dipakai: `->middleware('throttle:30,1')`.
6. **UI text dalam Bahasa Indonesia**: ikuti pola existing — "Dapatkan Gratis", "GRATIS", "Cara Akses Software", dst. Jangan campur dengan English kecuali memang istilah teknis (mis. "API", "endpoint").

Untuk dokumentasi lisensi produk berbayar, lihat [`LICENSE_ACCESS.md`](LICENSE_ACCESS.md).
