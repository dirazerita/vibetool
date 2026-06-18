# Changelog

Catatan perubahan VibeTool/PRODIG. Entri terbaru di atas.

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
