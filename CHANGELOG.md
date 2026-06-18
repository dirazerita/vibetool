# Changelog

Catatan perubahan VibeTool/PRODIG. Entri terbaru di atas.

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
