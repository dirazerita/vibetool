# VibeTool Android — Aplikasi Native

Aplikasi Android **100% native** (Kotlin + Jetpack Compose, **bukan WebView**)
untuk marketplace VibeTool.Id.

## Fitur

| Layar | Isi |
|---|---|
| Login / Daftar | Autentikasi Bearer token via API; register akun baru (status pending) |
| Beranda | Kartu saldo komisi + link afiliasi (salin 1 tap) + grid produk digital |
| Detail Produk | Gambar, harga & paket, tombol **Beli Sekarang** → checkout web via Chrome Custom Tabs (autologin otomatis, aman via signed URL 15 menit) |
| Komisi | Riwayat komisi (langsung / bonus upline / bagian kreator) |
| Lisensi | Kunci lisensi + salin, jumlah perangkat terkoneksi, **Reset Perangkat** dari HP |
| Tim | Daftar downline + status aktif/pending |
| Profil | Data akun, saldo, riwayat pembelian + download, logout |

## Arsitektur

- **UI**: Jetpack Compose + Material 3, tema dark premium (indigo-violet, konsisten dengan web)
- **Network**: Retrofit 2 + OkHttp (interceptor Bearer token) + Gson
- **Gambar**: Coil
- **Navigasi**: Navigation Compose, bottom bar 5 tab
- **Backend**: endpoint `api/app/*` di Laravel (lihat `routes/api.php` repo utama)

## Cara Buka di Android Studio

1. Buka Android Studio → **Open** → pilih folder `ANDROID NATIVE` ini.
2. Tunggu Gradle Sync selesai (AS otomatis mengunduh Gradle 8.10.2 dari
   `gradle/wrapper/gradle-wrapper.properties`).
3. Jalankan ke emulator / device (tombol ▶).

> Catatan: `gradle-wrapper.jar` sengaja tidak di-commit (file binary).
> Android Studio tidak membutuhkannya untuk sync/build. Kalau mau pakai
> `./gradlew` dari terminal, jalankan sekali: `gradle wrapper` (butuh Gradle
> terpasang) — atau cukup build lewat Android Studio.

## Konfigurasi Server

- URL server produksi ada di satu tempat:
  [`ApiClient.kt`](app/src/main/java/id/vibetool/app/data/ApiClient.kt) →
  `BASE_URL = "https://vibetool.id/"`.
- **Testing lokal** (php artisan serve) dari emulator: ganti jadi
  `http://10.0.2.2:8000/` dan tambahkan `android:usesCleartextTraffic="true"`
  pada tag `<application>` di `AndroidManifest.xml` (jangan lupa kembalikan
  sebelum rilis).

## Kebutuhan Backend

Aplikasi ini memakai endpoint yang ditambahkan bersamaan di repo Laravel:

- `POST /api/app/login` → Bearer token
- `GET  /api/app/products`, `GET /api/app/products/{slug}` (publik)
- `GET  /api/app/dashboard|licenses|commissions|team|purchases|me` (token)
- `POST /api/app/licenses/{id}/reset-devices`, `POST /api/app/logout` (token)
- `GET  /api/app/checkout-link/{slug}` → signed autologin URL untuk checkout
- `POST /api/auth/register` (endpoint lama, dipakai untuk daftar)

Pastikan migrasi `add_api_token_to_users` sudah dijalankan di server
(`php artisan migrate --force`).

## Rilis

1. Ganti `versionCode` / `versionName` di `app/build.gradle.kts`.
2. Build → Generate Signed Bundle/APK di Android Studio.
3. R8/ProGuard sudah dikonfigurasi (model data di-keep, lihat `proguard-rules.pro`).
