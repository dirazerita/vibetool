# Panduan Akses Lisensi Software

Dokumen ini menjelaskan cara kerja sistem **lisensi software** di Vibetool — mulai dari konfigurasi produk oleh admin, alokasi otomatis ke member setelah pembayaran, akses kunci lisensi oleh member, sampai validasi lisensi dari sisi aplikasi software/tool yang dijual.

> Sistem ini hanya aktif untuk produk dengan tipe **Software / Tool**. Produk bertipe **Digital (file/download)** tidak melalui flow lisensi.

---

## 1. Konsep Singkat

| Istilah | Penjelasan |
|---|---|
| **Software Product** | Produk dengan `product_type = software`. Tiap order paid otomatis mendapat satu kunci lisensi. |
| **License Key** | String unik per produk (format default `XXXX-XXXX-XXXX-XXXX`, huruf kapital). |
| **License Duration** | Masa berlaku lisensi yang ditetapkan di level produk: `1_month`, `6_months`, `1_year`, atau `lifetime`. |
| **Assigned License** | Lisensi yang sudah terikat ke `order_id` dan `user_id` (sudah diberikan ke member). |
| **Available License** | Lisensi tanpa `order_id` — stok manual yang di-upload admin tapi belum dialokasikan. |
| **Lifetime License** | Lisensi dengan `expires_at = NULL`, tidak pernah kedaluwarsa. |

---

## 2. Skema Database

Tabel `licenses` (lihat `database/migrations/2024_01_12_000002_create_licenses_table.php` dan migrasi tambahan `2024_01_15_000001_*`):

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `product_id` | FK → `products.id` | Cascade on delete |
| `key` | string | Kunci lisensi, unik per produk |
| `extra_info` | text nullable | Instruksi aktivasi (mis. URL aktivasi, catatan ke member) |
| `order_id` | FK → `orders.id` nullable | Null = stok tersedia, terisi = sudah dialokasikan |
| `user_id` | FK → `users.id` nullable | Pemilik lisensi (terisi saat dialokasikan) |
| `assigned_at` | timestamp nullable | Waktu lisensi dialokasikan ke order |
| `expires_at` | timestamp nullable | Null = lifetime, isi = kedaluwarsa pada waktu tsb |
| `timestamps` | | `created_at`, `updated_at` |

Constraint: `UNIQUE(product_id, key)` dan index `(product_id, order_id)`.

Tabel `products` punya kolom tambahan `license_duration` (default `lifetime`) yang menentukan masa berlaku lisensi otomatis untuk semua order produk tersebut.

---

## 3. Alur untuk Admin

### 3.1 Membuat Software Product dengan Lisensi

1. Buka **Admin Panel → Products → Tambah Produk** (`/admin/products/create`).
2. Pada **Tipe Produk**, pilih **Software / Tool (dengan lisensi)**.
3. Pada **Masa Berlaku Lisensi**, pilih salah satu:
   - `1 Bulan` — `expires_at = assigned_at + 1 month`
   - `6 Bulan` — `expires_at = assigned_at + 6 months`
   - `1 Tahun` — `expires_at = assigned_at + 1 year`
   - `Lifetime` — `expires_at = NULL`
4. Lengkapi data produk lainnya (harga, komisi, file/URL, dst.) lalu simpan.

> Jika `product_type` di-set ke `digital`, kolom `license_duration` akan otomatis disimpan sebagai `lifetime` dan flow lisensi tidak akan dijalankan untuk produk tsb.

### 3.2 Mengelola Stok Lisensi Manual

Halaman: **Admin → Lisensi** (`/admin/licenses`)

- Daftar berisi semua produk bertipe `software` dengan ringkasan: `Total Lisensi`, `Sudah Diberikan`, `Tersedia`, `Order Paid`, dan badge jumlah order paid yang belum dapat lisensi.
- Klik **Kelola** pada produk untuk masuk ke halaman detail (`/admin/licenses/{product}`).

Di halaman detail, admin bisa:

| Aksi | Endpoint | Deskripsi |
|---|---|---|
| **Tambah Lisensi (bulk)** | `POST /admin/licenses/{product}` | Paste 1 kunci per baris di textarea. Duplikat (kombinasi `product_id + key` yang sudah ada) dilewati otomatis. Bisa menambahkan `extra_info` (instruksi aktivasi) yang akan ikut tersimpan ke semua kunci di batch tsb. |
| **Generate Lisensi untuk Order** | `POST /admin/licenses/assign-order/{order}` | Generate kunci unik otomatis dan dialokasikan langsung ke order paid yang belum punya lisensi. |
| **Edit Masa Berlaku** | `PUT /admin/licenses/{license}` | Ubah `expires_at` per lisensi. Mendukung preset (`1_month`, `6_months`, `1_year`, `lifetime`) atau `custom` dengan datetime manual. |
| **Hapus Lisensi** | `DELETE /admin/licenses/{license}` | Hapus permanen. |

> Catatan: Format kunci yang di-generate otomatis adalah `XXXX-XXXX-XXXX-XXXX` (huruf kapital, alfanumerik). Lihat `LicenseController::generateUniqueLicenseKey()` dan `OrderPaymentService::generateUniqueLicenseKey()`. Sistem retry sampai mendapat kombinasi unik untuk `product_id` tsb.

---

## 4. Alur Alokasi Otomatis (Order Paid → License)

Trigger: order berubah status menjadi `paid`, baik via:

- **Xendit Webhook** (`POST /webhook/xendit`) — `WebhookController` memanggil `OrderPaymentService::markAsPaid()`.
- **Mark Paid Manual** oleh admin (`POST /admin/orders/{order}/mark-paid`).

Di dalam `OrderPaymentService::markAsPaid()` (dijalankan dalam transaksi DB):

1. `Order` di-update ke `status = paid`.
2. Komisi affiliator & upline dihitung dan dimasukkan ke `commissions` + saldo user.
3. `assignLicense($order)` dijalankan: **jika** `product_type === 'software'` **dan** order belum punya lisensi, maka:
   - Generate kunci unik via `generateUniqueLicenseKey($product->id)`.
   - Hitung `expires_at` dari `product->license_duration`.
   - Buat baris baru di `licenses` dengan `order_id`, `user_id`, `assigned_at = now()`, `expires_at`.

```
Order paid
   │
   ├─ processCommissions() ─ komisi direct + bonus upline
   └─ assignLicense() ─── kunci di-generate & disimpan ke licenses
```

> Untuk produk `digital` (bukan software), `assignLicense()` langsung return tanpa membuat lisensi.

---

## 5. Alur untuk Member (Pembeli)

Halaman: **Dashboard → Lisensi Saya** (`/dashboard/licenses`)

Yang ditampilkan:

- **Kartu lisensi** untuk tiap lisensi milik user, berisi:
  - Thumbnail/hero produk + judul
  - Badge tipe (`Software / Tool`) dan status (`Lifetime` / `Aktif` / `Kedaluwarsa`)
  - **Kunci Lisensi** (read-only) + tombol **Salin**
  - **Instruksi Aktivasi** (`extra_info`) bila admin mengisinya
  - Tombol **Download / Buka Link Produk** (kalau produk punya `file_path` atau `file_url`)
  - Tombol **Detail Produk**
- **Banner "Lisensi belum tersedia"** untuk order paid yang belum dialokasikan lisensi (mis. admin belum generate kunci, atau stok manual sedang habis).

Member tidak perlu klaim — lisensi otomatis muncul di halaman ini setelah pembayaran dikonfirmasi.

---

## 6. Validasi Lisensi dari Software Pihak Pembeli

Endpoint publik untuk validasi kunci lisensi:

```
POST /api/license/validate
Content-Type: application/json
```

### Request Body

| Field | Tipe | Wajib | Deskripsi |
|---|---|---|---|
| `key` | string | ya | Kunci lisensi (mis. `ABCD-1234-EFGH-5678`). |
| `product_slug` | string | tidak | Bila diisi, validasi akan dibatasi hanya untuk produk dengan `slug` tsb (mencegah satu kunci dipakai untuk produk lain). |

### Contoh

```bash
curl -X POST https://<host>/api/license/validate \
  -H "Content-Type: application/json" \
  -d '{
    "key": "ABCD-1234-EFGH-5678",
    "product_slug": "vibetool-pro"
  }'
```

### Response

**200 OK — Lisensi valid**

```json
{
  "valid": true,
  "message": "Lisensi valid.",
  "license": {
    "key": "ABCD-1234-EFGH-5678",
    "product": { "id": 1, "title": "Vibetool Pro", "slug": "vibetool-pro" },
    "user": { "id": 42, "name": "Budi", "email": "budi@example.com" },
    "assigned_at": "2025-01-10T03:21:00+00:00",
    "expires_at": "2026-01-10T03:21:00+00:00",
    "is_lifetime": false
  }
}
```

**404 Not Found — Kunci tidak ditemukan atau belum dialokasikan ke order**

```json
{
  "valid": false,
  "error": "license_not_found",
  "message": "Kunci lisensi tidak ditemukan atau belum dialokasikan."
}
```

**403 Forbidden — Lisensi kedaluwarsa**

```json
{
  "valid": false,
  "error": "license_expired",
  "message": "Lisensi sudah kedaluwarsa.",
  "license": { "...": "..." }
}
```

### Catatan Implementasi

- Endpoint **tidak memerlukan autentikasi** — aman dipanggil dari aplikasi software end-user, namun jangan kirim informasi sensitif lewat URL.
- Kunci yang masih berstatus *available* (`order_id IS NULL`) **tidak akan tervalidasi** — hanya kunci yang sudah terikat ke order yang dianggap valid.
- `is_lifetime = true` berarti `expires_at` null dan lisensi tidak akan pernah kedaluwarsa.
- Untuk lisensi expired, response **tetap menyertakan** detail lisensi sehingga aplikasi software bisa menampilkan tanggal kedaluwarsa dan info user.
- Pertimbangkan untuk melakukan caching lokal di sisi aplikasi software (mis. simpan hasil validasi terakhir + tanggal expired) untuk mengurangi panggilan API berulang.

---

## 7. Status & State Machine Lisensi

```
                    ┌─────────────────┐
   admin tambah ──► │   AVAILABLE     │  order_id = NULL
   stok manual      │  (stok bebas)   │  user_id  = NULL
                    └────────┬────────┘
                             │ admin assign / auto-assign
                             ▼
                    ┌─────────────────┐
                    │    ASSIGNED     │  order_id  = <id>
                    │   (terpakai)    │  user_id   = <id>
                    │                 │  assigned_at = now()
                    └────────┬────────┘
                             │
              ┌──────────────┴──────────────┐
              ▼                             ▼
     ┌─────────────────┐           ┌─────────────────┐
     │     ACTIVE      │           │    LIFETIME     │
     │ expires_at>now  │           │ expires_at=NULL │
     └────────┬────────┘           └─────────────────┘
              │ waktu lewat
              ▼
     ┌─────────────────┐
     │     EXPIRED     │  isExpired() = true
     └─────────────────┘
```

Helper di model `App\Models\License`:

- `isAssigned()` — `order_id !== null`
- `isLifetime()` — `expires_at === null`
- `isExpired()` — `expires_at !== null && expires_at->isPast()`

---

## 8. Ringkasan Endpoint

### Admin (auth + middleware `admin`)

| Method | URL | Aksi |
|---|---|---|
| GET | `/admin/licenses` | Daftar produk software + ringkasan |
| GET | `/admin/licenses/{product}` | Detail lisensi per produk |
| POST | `/admin/licenses/{product}` | Tambah lisensi bulk (paste keys) |
| POST | `/admin/licenses/assign-order/{order}` | Generate & alokasikan lisensi ke order paid |
| PUT | `/admin/licenses/{license}` | Update masa berlaku lisensi |
| DELETE | `/admin/licenses/{license}` | Hapus lisensi |

### Member (auth + middleware `active`)

| Method | URL | Aksi |
|---|---|---|
| GET | `/dashboard/licenses` | Daftar lisensi milik user + order pending |

### Publik

| Method | URL | Aksi |
|---|---|---|
| POST | `/api/license/validate` | Validasi kunci lisensi dari software pihak end-user |

---

## 9. FAQ Singkat

**T: Apakah satu order bisa punya lebih dari satu lisensi?**
A: Tidak. Relasi `Order → License` adalah `hasOne`, dan `assignLicense()` hanya berjalan sekali (cek `$order->license`). Jika butuh multi-license per order, perlu modifikasi.

**T: Bagaimana cara reset kunci lisensi member?**
A: Hapus lisensi via Admin → Lisensi → Hapus. Lalu klik **Generate Lisensi** lagi pada order tsb di banner "Order paid yang belum mendapat lisensi" untuk membuat kunci baru.

**T: Apakah `extra_info` per-lisensi atau global?**
A: Per-lisensi (kolom di tabel `licenses`). Saat admin add bulk via halaman lisensi, nilai `extra_info` di-apply ke semua kunci di batch tsb. Auto-generate (dari order paid) **tidak** mengisi `extra_info`.

**T: Kalau `license_duration` produk diubah, apakah lisensi lama ikut berubah?**
A: Tidak. `license_duration` hanya dipakai saat lisensi pertama kali dibuat untuk menghitung `expires_at`. Lisensi yang sudah ada bisa diperpanjang manual lewat Edit Masa Berlaku.

**T: Apakah validator publik bocor data user?**
A: Endpoint mengembalikan `name` & `email` user pemilik lisensi. Jika privasi jadi concern, batasi/anonimkan field ini di `LicenseValidationController::formatLicense()`.

---

## 10. File Referensi

- Migrasi: `database/migrations/2024_01_12_000002_create_licenses_table.php`, `database/migrations/2024_01_15_000001_add_license_duration_to_products_and_expires_at_to_licenses.php`
- Model: `app/Models/License.php`, `app/Models/Product.php`, `app/Models/Order.php`
- Admin Controller: `app/Http/Controllers/Admin/LicenseController.php`
- Member Controller: `app/Http/Controllers/Dashboard/LicenseController.php`
- API Validator: `app/Http/Controllers/Api/LicenseValidationController.php`
- Service alokasi otomatis: `app/Services/OrderPaymentService.php` (method `assignLicense`)
- Routes: `routes/web.php`, `routes/api.php`
- Views Admin: `resources/views/admin/licenses/index.blade.php`, `resources/views/admin/licenses/show.blade.php`
- View Member: `resources/views/dashboard/licenses.blade.php`
