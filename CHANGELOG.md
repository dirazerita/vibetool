# Changelog

Catatan perubahan VibeTool/PRODIG. Entri terbaru di atas.

## 2026-06-27 — feat: Custom HTML di landing page editor

**Kebutuhan:** Admin dan member yang upload produk perlu bisa menulis kode HTML
murni untuk landing page, memudahkan yang sudah terbiasa kode HTML dan ingin
kontrol penuh atas tampilan.

**Implementasi:**
- **Migration**: kolom `custom_html` (text, nullable) di `product_landing_pages`.
- **Model**: `custom_html` di fillable `ProductLandingPage`.
- **Controller** (admin & dashboard): validasi `nullable|string`, sanitasi via
  `Purifier::clean(..., 'landing_content')` supaya aman dari XSS tapi tetap
  mengizinkan tag HTML standar.
- **View editor**: section baru "Custom HTML" dengan textarea monospace 16 baris
  di bawah "Konten Utama", sebelum "Galeri Gambar". Disimpan bersama konten utama
  (tombol "Simpan Konten Utama").
- **Public landing page**: custom HTML dirender dengan `{!! $landingPage->custom_html !!}`
  setelah section "Tentang Produk", sebelum package picker.

**File:**
- `database/migrations/2026_06_27_100000_add_custom_html_to_product_landing_pages.php` (baru).
- `app/Models/ProductLandingPage.php` — fillable.
- `app/Http/Controllers/Admin/LandingPageController.php` — validasi + sanitasi + save.
- `app/Http/Controllers/Dashboard/LandingPageController.php` — idem.
- `resources/views/admin/products/landing-page.blade.php` — section Custom HTML.
- `resources/views/dashboard/products/landing-page.blade.php` — idem.
- `resources/views/product/landing.blade.php` — render.
- `tests/Feature/LandingCustomHtmlTest.php` (baru).

**Deploy:** ADA migration — setelah pull jalankan `php artisan migrate --force`.
Tidak ada perubahan aset.

## 2026-06-27 — feat: lisensi otomatis untuk pembuat produk saat disetujui admin

**Kebutuhan:** Member yang upload produk software perlu lisensi untuk produknya
sendiri agar bisa langsung test, tanpa harus membeli produknya.

**Implementasi:**
- `OrderPaymentService::assignLicenseToCreator(Product $product)` — method
  publik baru yang membuat lisensi untuk pembuat produk TANPA order:
  - Hanya untuk produk tipe `software`
  - Hanya kalau produk punya `created_by` (produk yang diupload member)
  - Cek duplikat: tidak akan buat lisensi kalau pembuat sudah punya lisensi
  - Lisensi lifetime (mengikuti `license_duration` produk)
  - Key digenerate dengan algoritma yang sama dengan pembelian normal
- `Admin\ProductController::approve()` — setelah approve, panggil
  `assignLicenseToCreator()` untuk langsung memberikan lisensi ke pembuat.
- Lisensi muncul di halaman "Lisensi Saya" member (`/dashboard/licenses`) beserta
  license key-nya, siap di-test.

**File:**
- `app/Services/OrderPaymentService.php` — method `assignLicenseToCreator()`.
- `app/Http/Controllers/Admin/ProductController.php` — tambah import + panggil
  di `approve()`.
- `tests/Feature/AutoLicenseForProductCreatorTest.php` (baru).

**Deploy:** Tidak ada migration, tidak ada perubahan aset; cukup pull biasa.

## 2026-06-27 — feat: member bisa mengelola Landing Page & Video Tutorial untuk produk sendiri

**Kebutuhan:** Member yang upload produk sebelumnya tidak bisa membuat landing page
dan video tutorial untuk produknya (hanya admin). Di menu Produk Saya juga hanya
ada tombol Edit dan Hapus.

**Implementasi:**
- **2 controller baru di dashboard**:
  - `Dashboard\LandingPageController` — mirror `Admin\LandingPageController`
    (edit/update landing page, upload/delete/order galeri gambar, CRUD testimonial,
    toggle testimonial). Pengecekan kepemilikan: `$product->created_by === auth()->id()`
    dan `canUploadProduct()`.
  - `Dashboard\MemberVideoTutorialController` — mirror `Admin\VideoTutorialController`
    (CRUD video tutorial, toggle, reorder). Pengecekan kepemilikan yang sama.
- **Routes** di bawah `dashboard` prefix:
  - `/products/{product}/landing-page` + sub-routes (images, testimonials, reorder)
  - `/products/{product}/video-tutorials` + sub-routes (store, update, destroy, toggle, reorder)
  Semua route dicek via controller authorization.
- **View**: `dashboard/products/landing-page.blade.php` dan `video-tutorials.blade.php`
  (adaptasi dari admin views, menggunakan `layouts.dashboard` dan dashboard route names).
- **Produk Saya** (`dashboard/member-products`): setiap product card kini punya
  tombol **Landing Page**, **Video Tutorial** (hanya untuk produk approved), dan
  **Preview** (link ke halaman publik produk) — selain Edit dan Hapus.
- **Sidebar**: menu baru **Landing Page** di bawah blok `canUploadProduct()`,
  mengarah ke `dashboard.member-products`.

**Keamanan:** Pengecekan `canUploadProduct()` + `created_by === auth()->id()` di
setiap method controller. Akses produk member lain → 403.

**File:**
- `app/Http/Controllers/Dashboard/LandingPageController.php` (baru)
- `app/Http/Controllers/Dashboard/MemberVideoTutorialController.php` (baru)
- `routes/web.php` — import + 15 route baru
- `resources/views/dashboard/products/landing-page.blade.php` (baru)
- `resources/views/dashboard/products/video-tutorials.blade.php` (baru)
- `resources/views/dashboard/member-products.blade.php` — tombol baru
- `resources/views/layouts/dashboard.blade.php` — menu sidebar baru
- `tests/Feature/MemberLandingAndVideoTutorialTest.php` (baru)

**Deploy:** Tidak ada migration, tidak ada perubahan aset. Cukup pull biasa.

## 2026-06-18 — feat: upload bukti transfer di penarikan komisi

**Kebutuhan:** Saat memproses pembayaran komisi (penarikan), admin bisa
mengunggah foto bukti transfer, sehingga member yang menarik komisinya bisa
melihat bukti transfer dari admin.

**Implementasi:**
- **Migration**: kolom baru `transfer_proof` (nullable) di tabel `withdrawals`.
- **Model `Withdrawal`**: `transfer_proof` di fillable + helper `hasTransferProof()`
  dan `transferProofUrl()`.
- **Admin** (`/admin/withdrawals`):
  - Form "Setujui" kini punya input file bukti transfer (opsional) — sekali klik
    menyetujui + menyimpan bukti.
  - Untuk penarikan yang sudah disetujui, ada form **Upload / Ganti bukti
    transfer** (jika admin lupa unggah saat approve).
  - Kolom "Bukti Transfer" dengan link "Lihat".
  - Endpoint baru `POST /admin/withdrawals/{withdrawal}/upload-proof`.
  - Validasi: image (jpg/jpeg/png/webp), maksimal 4MB. Disimpan di disk `public`
    (`storage/app/public/transfer-proofs`).
- **Member** (`/dashboard/withdrawals`): kolom "Bukti Transfer" — tombol **Lihat
  Bukti** bila sudah ada, atau info "Menunggu bukti dari admin" untuk penarikan
  yang sudah disetujui tapi buktinya belum diunggah.

**File:**
- `database/migrations/2026_06_18_150000_add_transfer_proof_to_withdrawals.php` (baru).
- `app/Models/Withdrawal.php` — fillable + helper.
- `app/Http/Controllers/Admin/WithdrawalController.php` — `approve()` terima
  upload + method `uploadProof()`.
- `routes/web.php` — route upload-proof.
- `resources/views/admin/withdrawals.blade.php` — form upload + kolom bukti.
- `resources/views/dashboard/withdrawals.blade.php` — kolom bukti untuk member.
- `tests/Feature/WithdrawalTransferProofTest.php` (baru).

**Deploy:** ADA migration — setelah pull jalankan `php artisan migrate --force`.
Pastikan symlink storage aktif (`php artisan storage:link`) agar bukti transfer
bisa diakses publik. Tidak ada perubahan `resources/js`/`resources/css`.

## 2026-06-18 — feat: komisi dibayarkan di halaman komisi admin

**Kebutuhan:** Admin perlu tahu berapa komisi yang sudah dibayarkan ke member,
beserta detail untuk member mana.

**Konsep:** "Komisi dibayarkan" = total penarikan (withdrawal) yang sudah
DISETUJUI admin — yaitu uang yang benar-benar cair ke rekening member. (Kolom
`commission.status` tidak dipakai untuk ini karena komisi selalu `approved` saat
dikreditkan ke saldo; pembayaran nyata terjadi lewat penarikan.)

**Implementasi:**
- **Halaman daftar komisi** (`/admin/commissions`):
  - Kartu summary baru **"Komisi Dibayarkan"** (total penarikan disetujui) +
    info "Menunggu" (penarikan pending).
  - Kolom **"Dibayarkan"** per member di tabel.
- **Halaman detail member** (`/admin/commissions/{user}`):
  - Stat **"Dibayarkan"** (+ menunggu).
  - Tabel baru **"Riwayat Pembayaran Komisi"**: tanggal, nominal, rekening tujuan,
    status (Disetujui/Dibayarkan, Menunggu, Ditolak), catatan.

**File:**
- `app/Http/Controllers/Admin/CommissionController.php` — agregat `paid_out`/
  `pending_payout` di index & show + data `payouts`.
- `resources/views/admin/commissions/index.blade.php` — kartu + kolom.
- `resources/views/admin/commissions/show.blade.php` — stat + tabel pembayaran.
- `tests/Feature/AdminCommissionPaidOutTest.php` (baru).

**Deploy:** Tidak ada migration, tidak ada perubahan aset; cukup pull biasa.

## 2026-06-18 — feat: status verifikasi email member di admin + filter

**Kebutuhan:** Admin perlu tahu member mana yang sudah/belum memverifikasi email.

**Implementasi:**
- **Kolom "Verifikasi Email"** di tabel `/admin/members`: badge "Terverifikasi"
  (hijau, dengan tanggal verifikasi di tooltip) atau "Belum" (kuning).
- **Filter tabs**: Semua / Email Terverifikasi / Belum Verifikasi, masing-masing
  dengan badge jumlah. Filter ini bisa dikombinasikan dengan pencarian yang ada.
- Datanya dari kolom `email_verified_at` yang sudah ada (tanpa migration).

**File:**
- `app/Http/Controllers/Admin/MemberController.php` — filter `verification` +
  hitung `verifiedCount` / `unverifiedCount`.
- `resources/views/admin/members.blade.php` — kolom badge + filter tabs.
- `tests/Feature/AdminMemberEmailVerificationTest.php` (baru).

**Deploy:** Tidak ada migration, tidak ada perubahan aset; cukup pull biasa.

## 2026-06-18 — feat: node Tim/Downline klikable + halaman detail member tim

**Kebutuhan:** Item member di pohon Tim/Downline (member area) dibuat klikable;
saat diklik menampilkan detail member tersebut.

**Implementasi:**
- **Node klikable**: node level 2 (tim langsung) & level 3 (downline tim) di
  `dashboard/team.blade.php` kini berupa link ke halaman detail, dengan efek
  hover.
- **Halaman detail** (`/dashboard/team/{member}`, `dashboard.team.show`):
  - Header: nama, email, tanggal gabung, badge Tim Langsung / Downline Tim, dan
    tombol **Hubungi via WhatsApp** (bila nomor ada).
  - Ringkasan: jumlah penjualan (sebagai affiliator) + omzet, jumlah produk yang
    dibeli, dan **komisi yang dihasilkan untuk viewer** dari pembelian member ini.
  - Tabel produk yang dibeli member (tanggal, produk, jumlah, komisi untuk viewer).
  - Daftar sub-downline member, masing-masing bisa diklik untuk drill-down.
- **Otorisasi**: hanya boleh melihat member yang merupakan keturunan (downline
  langsung/tidak langsung) dari user yang login. Akses ke diri sendiri, upline,
  atau member di luar tim → 403. Penelusuran rantai upline dibatasi kedalaman 50.

**File:**
- `app/Http/Controllers/Dashboard/TeamController.php` — method `show()` + guard
  `isDescendantOf()`.
- `routes/web.php` — route `dashboard.team.show`.
- `resources/views/dashboard/team.blade.php` — node jadi link.
- `resources/views/dashboard/team-show.blade.php` (baru).
- `tests/Feature/TeamMemberDetailTest.php` (baru).

**Deploy:** Tidak ada migration, tidak ada perubahan aset; cukup pull biasa.

## 2026-06-18 — feat: halaman "Pembelian Tim" di member area (analisa downline + follow-up)

**Kebutuhan:** Member/upline ingin tahu downline mana yang sudah membeli, produk
apa yang mereka beli, dan berapa komisi yang dihasilkan untuk dia — serta downline
mana yang belum membeli supaya bisa di-follow-up.

**Implementasi:**
- **Menu baru** "Pembelian Tim" di sidebar dashboard (`/dashboard/team-purchases`,
  `dashboard.team-purchases`). Berbeda dari menu "Tim / Downline" yang menampilkan
  downline sebagai PENJUAL; halaman ini menampilkan downline sebagai PEMBELI.
- **Ringkasan**: total downline, sudah membeli, belum membeli, dan total komisi
  yang member hasilkan dari pembelian tim-nya.
- **Filter**: Semua / Sudah Membeli / Perlu Follow-up (downline tanpa pembelian
  lunas).
- **Per downline**: status beli, jumlah pembelian, total belanja, komisi yang
  masuk ke member, tanggal pembelian terakhir, link WhatsApp untuk follow-up, dan
  daftar produk yang dibeli (expand via Alpine) lengkap dengan komisi per produk.

**Catatan teknis:**
- "Sudah membeli" = punya minimal 1 order `paid` dengan `amount > 0`.
- Komisi yang dihitung HANYA milik member yang sedang login (tidak termasuk bonus
  upline orang lain), diambil dari `commissions` yang terkait order downline.
- Agregasi produk hanya untuk downline di halaman aktif (paginasi 15) agar ringan;
  statistik ringkas & komisi total dihitung lewat query agregat, bukan load relasi.
- Hanya downline LANGSUNG (`upline_id = member`) yang ditampilkan.

**File:**
- `app/Http/Controllers/Dashboard/TeamPurchaseController.php` (baru).
- `routes/web.php` — route `dashboard.team-purchases`.
- `resources/views/layouts/dashboard.blade.php` — menu sidebar "Pembelian Tim".
- `resources/views/dashboard/team-purchases.blade.php` (baru).
- `tests/Feature/TeamPurchasesTest.php` (baru).

**Deploy:** Tidak ada migration, tidak ada perubahan `resources/js`/`resources/css`
(Alpine sudah di-bundle); tidak perlu rebuild aset.

## 2026-06-18 — feat: koreksi komisi pesanan lama (saran pemilik kupon, filter, guard saldo, dropdown lazy)

Penyempurnaan fitur "ganti affiliator pesanan" (lihat entri 2026-06-15) untuk
mengoreksi pesanan **lama** yang memakai kupon upline tetapi affiliatornya tidak
terekam — kasus yang dilaporkan upline karena komisinya tidak masuk.

**1. Saran pemilik kupon (1-klik):**
- Untuk pesanan berkupon tanpa affiliator, muncul tombol **"Tetapkan pemilik
  kupon: <nama>"** di kolom Affiliator. Sekali klik, affiliator di-set ke pemilik
  kupon dan upline-nya otomatis dapat bonus upline; komisi langsung dicairkan.
- Resolusi pemilik kupon deterministik: utamakan upline pembeli bila dia pemilik
  kupon, jika tidak ambil pemilik kupon dengan id terkecil; pembeli & pembuat
  produk selalu dikecualikan. Di-batch (eager-load `members`) agar tidak N+1.
- Route: `POST /admin/orders/{order}/assign-coupon-owner`
  (`admin.orders.assign-coupon-owner`) + `OrderController::assignCouponOwner()`.

**2. Filter "Perlu Koreksi Komisi":**
- Tab filter di `/admin/orders` (`?filter=needs_attribution`) menampilkan hanya
  pesanan **lunas + berkupon + tanpa affiliator**, lengkap dengan badge jumlah.
  Memudahkan admin menemukan semua pesanan bermasalah sekaligus.

**3. Guard reversal komisi (review fix):**
- `reassignAffiliate()` kini mengembalikan array peringatan. Saat reversal,
  saldo penerima lama dikurangi PERSIS sebesar komisi yang dibalik (bukan
  di-floor ke 0, karena flooring akan menghapus earning sah dari order lain).
  Bila komisi sudah ditarik, saldo bisa minus — itu benar secara akuntansi
  (mewakili hutang ke platform, terbayar otomatis dari komisi berikutnya;
  `WithdrawalController` memblokir penarikan saat jumlah > saldo). Selisihnya
  dilaporkan ke admin (flash `warning`) untuk rekonsiliasi manual.
- `assignCouponOwner()` menolak order yang **sudah** punya affiliator (cegah
  double-submit / POST langsung yang tak sengaja membalik komisi yang benar);
  penggantian eksplisit tetap lewat tombol "Ubah".

**4. Dropdown member lazy-load (review fix performa):**
- Daftar member tidak lagi dirender penuh per baris (sebelumnya 15×N `<option>`).
  Modal kini memuat member via endpoint pencarian `GET
  /admin/orders/members/search` (limit 30, filter nama/email) saat dibuka,
  dengan komponen Alpine `affiliatePicker`.

**File:**
- `app/Http/Controllers/Admin/OrderController.php` — `assignCouponOwner()`,
  `searchMembers()`, filter di `index()`, resolusi pemilik kupon, handle warning.
- `app/Services/OrderPaymentService.php` — `reassignAffiliate()` guard saldo
  negatif + return warnings.
- `routes/web.php` — route search members & assign-coupon-owner.
- `resources/views/admin/orders.blade.php` — tab filter, tombol saran kupon,
  dropdown lazy (Alpine), flash warning.
- `tests/Feature/OrderAffiliateReassignTest.php` — test tambahan (saran kupon,
  prioritas upline, guard saldo negatif, endpoint search, filter).

**Catatan:** Tidak ada migration. Blade memuat komponen Alpine `affiliatePicker`
via `<script>` inline (Alpine sudah di-bundle Vite); tidak ada perubahan
`resources/js`/`resources/css` sehingga tidak perlu rebuild aset.

## 2026-06-15 — feat: admin bisa ganti affiliator pesanan + auto-set upline & hitung ulang komisi

**Kebutuhan:** Admin perlu bisa mengubah affiliator sebuah pesanan dari halaman
`/admin/orders`, supaya affiliator yang seharusnya bisa mendapat komisi, dan
upline affiliator tsb otomatis ikut dapat bonus upline.

**Implementasi:**
- **UI** (`resources/views/admin/orders.blade.php`): kolom "Affiliator" kini punya
  tombol **Ubah** yang membuka modal (Alpine.js) berisi dropdown member untuk
  memilih affiliator baru, plus opsi "Tanpa affiliator". Upline affiliator
  ditampilkan di bawah nama. Tambah util `[x-cloak]` agar modal tidak flash
  sebelum Alpine init.
- **Route** (`routes/web.php`): `PUT /admin/orders/{order}/affiliate`
  (`admin.orders.update-affiliate`).
- **Controller** (`Admin/OrderController.php`): `updateAffiliate()` — validasi
  `affiliate_id` (`exists:users,id`, boleh kosong), guard affiliator ≠ pembeli &
  ≠ pembuat produk, lalu panggil service. `index()` kini mem-preload relasi
  `uplineUser` + daftar `members` untuk dropdown.
- **Service** (`OrderPaymentService.php`): method baru `reassignAffiliate()` —
  (1) reverse komisi `direct`/`upline` lama (saldo penerima lama dikurangi,
  baris komisi dihapus), (2) set `affiliate_id` + `upline_id` (dari `upline_id`
  affiliator baru), (3) bila order sudah `paid`, hitung ulang & kreditkan komisi
  baru. Komisi `creator` tidak disentuh. Logika komisi affiliate/upline
  diekstrak ke `processAffiliateCommissions()` agar dipakai bersama dengan
  `markAsPaid()`. Tarif komisi tetap dievaluasi pada `$order->created_at`.

**Catatan:** Semua operasi dibungkus `DB::transaction`. Tidak ada migration.

**File:**
- `resources/views/admin/orders.blade.php` — UI ubah affiliator (modal).
- `routes/web.php` — route update-affiliate.
- `app/Http/Controllers/Admin/OrderController.php` — `updateAffiliate()` + preload.
- `app/Services/OrderPaymentService.php` — `reassignAffiliate()` + refactor komisi.
- `tests/Feature/OrderAffiliateReassignTest.php` — regression test (baru).

## 2026-06-15 — fix: komisi tidak masuk saat pakai kupon upline tanpa link ref

**Masalah:** Downline checkout pakai kupon milik upline-nya (mis. OmBags). Diskon
kupon ter-apply, tapi komisi tidak masuk ke pemilik kupon (OmBags) dan upline-nya
(rynz2018@gmail.com) tidak dapat bonus upline.

**Akar masalah:** Di `CheckoutController::process()`, atribusi `affiliate_id` /
`upline_id` hanya di-resolve dari cookie/`?ref=` link. Sementara resolusi diskon
kupon sudah diperluas (commit `99f66db` & `fa3647b`) untuk mempertimbangkan
`upline_id` + kepemilikan kupon (`coupon_members`). Akibatnya, ketika downline
checkout dari menu produk dashboard (tanpa klik link `?ref=`), kupon upline tetap
ter-apply tapi `affiliate_id`/`upline_id` tetap `null` — sehingga
`OrderPaymentService::processCommissions()` tidak membuat baris komisi sama sekali.
Ini regresi: sebelum kupon diperluas, atribusi & diskon sama-sama hanya dari `ref`.

**Perbaikan:** Tambah fallback atribusi komisi di `process()`. Bila tidak ada
affiliate dari `ref` TAPI pembeli memakai kupon milik member lain, komisi
diatribusikan ke pemilik kupon (sebagai affiliate) dan upline pemilik kupon
(sebagai bonus upline). Prioritas pemilik kupon: `referrer` (yang sudah
mempertimbangkan `ref_code`/`upline_id`/`auto_coupon_member_id`) bila dia salah
satu pemilik kupon, jika tidak ambil pemilik kupon mana pun selain pembeli.
Guard "pembuat produk tidak boleh jadi affiliate/upline" tetap berlaku untuk
atribusi hasil kupon ini.

**File:**
- `app/Http/Controllers/CheckoutController.php` — fallback atribusi komisi via pemilik kupon.
- `tests/Feature/CouponCommissionAttributionTest.php` — regression test (baru).
