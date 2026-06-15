# Changelog

Catatan perubahan VibeTool/PRODIG. Entri terbaru di atas.

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
