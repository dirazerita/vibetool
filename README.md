# PRODIG - Marketplace Produk Digital

Aplikasi web marketplace produk digital dengan sistem membership & afiliasi 1-level, dibangun menggunakan Laravel 11.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL
- **Frontend:** Blade Templates + Tailwind CSS
- **Authentication:** Laravel Breeze
- **Payment Gateway:** Xendit (Invoice & Webhook)

## Fitur

- **Marketplace Produk Digital** — Jual dan beli produk digital (e-book, template, video course, dll)
- **Sistem Membership** — Setiap member otomatis menjadi affiliator
- **Sistem Afiliasi 1-Level:**
  - Komisi langsung 30% untuk affiliator
  - Bonus upline 10% untuk yang merekrut affiliator
  - Tracking via cookie 30 hari (`?ref=KODE_REFERRAL`)
  - Anti-fraud: tidak bisa beli lewat link afiliasi sendiri
- **Payment Gateway Xendit** — Invoice otomatis & webhook callback
- **Download Produk** — Akses download setelah pembayaran sukses via token unik
- **Dashboard Member** — Overview saldo, link afiliasi, riwayat komisi, tim/downline, penarikan saldo, pengaturan profil
- **Admin Panel** — Kelola produk (CRUD), pesanan, member, dan proses penarikan

## Instalasi

```bash
# Clone repository
git clone https://github.com/dirazerita-beep/prodig.git
cd prodig

# Install dependencies
composer install
npm install && npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env
# DB_DATABASE=prodig
# DB_USERNAME=root
# DB_PASSWORD=

# Konfigurasi Xendit di .env
# XENDIT_SECRET_KEY=your_xendit_secret_key
# XENDIT_WEBHOOK_TOKEN=your_webhook_verification_token

# Jalankan migrasi & seeder
php artisan migrate --seed

# Jalankan server
php artisan serve
```

## Akun Default

| Role  | Email            | Password |
|-------|------------------|----------|
| Admin | admin@prodig.id  | admin123 |

## Produk Contoh (Seeder)

1. E-Book Panduan Digital Marketing — Rp 150.000
2. Template Website Premium — Rp 250.000
3. Video Course Laravel Mastery — Rp 500.000

## Struktur URL

### Publik
| URL | Deskripsi |
|-----|-----------|
| `/` | Beranda (daftar produk) |
| `/p/{slug}?ref={kode}` | Detail produk |
| `/checkout/{slug}` | Halaman checkout |
| `/register?ref={kode}` | Daftar member |
| `/login` | Login |
| `/download/{token}` | Download produk |

### Dashboard Member (`/dashboard`)
| URL | Deskripsi |
|-----|-----------|
| `/dashboard` | Overview saldo & statistik |
| `/dashboard/products` | Link afiliasi |
| `/dashboard/commissions` | Riwayat komisi |
| `/dashboard/team` | Daftar downline |
| `/dashboard/withdrawals` | Penarikan saldo |
| `/dashboard/settings` | Profil & info bank |

### Admin Panel (`/admin`)
| URL | Deskripsi |
|-----|-----------|
| `/admin` | Overview platform |
| `/admin/products` | CRUD produk |
| `/admin/orders` | Semua pesanan |
| `/admin/members` | Semua member |
| `/admin/withdrawals` | Proses penarikan |

## Xendit Webhook

Endpoint: `POST /webhook/xendit`

Pastikan URL webhook dikonfigurasi di dashboard Xendit Anda. Webhook akan memproses:
- Status `PAID` — Konfirmasi pembayaran & hitung komisi
- Status `EXPIRED` — Update status order ke expired

## Database Schema

- `users` — Data member (termasuk referral_code, upline_id, balance, role)
- `products` — Data produk digital
- `orders` — Data pesanan (termasuk affiliate tracking)
- `commissions` — Data komisi (direct & upline)
- `withdrawals` — Data penarikan saldo

## Lisensi

MIT
