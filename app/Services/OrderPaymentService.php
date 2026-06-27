<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderPaymentService
{
    public function markAsPaid(Order $order): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $this->processCommissions($order);
            $this->assignLicense($order);
        });
    }

    public function markAsExpired(Order $order): void
    {
        if (in_array($order->status, ['paid', 'expired'], true)) {
            return;
        }

        $order->update(['status' => 'expired']);
    }

    /**
     * Ganti affiliator (dan otomatis upline-nya) untuk sebuah order.
     *
     * Dipakai admin di halaman pesanan untuk mengoreksi atribusi komisi.
     * Bila $affiliateId null, atribusi affiliate/upline dihapus.
     *
     * Alur:
     * 1. Reverse komisi direct & upline yang sudah ada (kembalikan saldo
     *    penerima lama). Komisi tipe 'creator' TIDAK disentuh karena tidak
     *    bergantung pada affiliator.
     * 2. Resolve affiliator baru + upline-nya (upline = upline_id affiliator).
     *    Guard: pembuat produk tidak boleh jadi affiliate/upline produknya
     *    sendiri, dan affiliator tidak boleh sama dengan pembeli.
     * 3. Update kolom affiliate_id & upline_id di order.
     * 4. Bila order sudah 'paid', hitung ulang & kreditkan komisi baru.
     *    Bila masih 'pending', komisi akan dihitung nanti saat markAsPaid().
     *
     * Mengembalikan array berisi pesan peringatan (mis. saldo penerima lama
     * tidak cukup saat reversal karena komisi sudah ditarik) untuk ditampilkan
     * ke admin. Array kosong berarti tidak ada peringatan.
     *
     * @return string[]
     */
    public function reassignAffiliate(Order $order, ?int $affiliateId): array
    {
        return DB::transaction(function () use ($order, $affiliateId) {
            $warnings = [];

            $product = $order->product;
            $creatorId = $product && $product->created_by ? (int) $product->created_by : null;

            $newAffiliate = $affiliateId ? User::find($affiliateId) : null;

            // Affiliator tidak boleh sama dengan pembeli.
            if ($newAffiliate && (int) $newAffiliate->id === (int) $order->user_id) {
                $newAffiliate = null;
            }

            $resolvedAffiliateId = $newAffiliate?->id;
            $resolvedUplineId = $newAffiliate?->upline_id;

            // Pembuat produk tidak boleh jadi affiliate/upline untuk produknya sendiri.
            if ($creatorId) {
                if ((int) $resolvedAffiliateId === $creatorId) {
                    $resolvedAffiliateId = null;
                }
                if ((int) $resolvedUplineId === $creatorId) {
                    $resolvedUplineId = null;
                }
            }

            // 1. Reverse komisi direct & upline lama.
            $existing = Commission::where('order_id', $order->id)
                ->whereIn('type', ['direct', 'upline'])
                ->get();

            foreach ($existing as $commission) {
                $recipient = User::find($commission->user_id);
                if ($recipient) {
                    $currentBalance = (float) $recipient->balance;
                    $reverseAmount = (float) $commission->amount;

                    // Selalu kurangi saldo PERSIS sebesar komisi yang dibalik.
                    // Jangan di-floor ke 0: saldo penerima bisa memuat earning
                    // SAH dari order lain, dan flooring akan menghapusnya.
                    //
                    // Bila komisi ini sudah (sebagian) ditarik, hasilnya bisa
                    // negatif — itu benar secara akuntansi: penerima berhutang
                    // ke platform karena uangnya sudah cair, dan hutang itu akan
                    // otomatis terbayar dari komisi berikutnya. WithdrawalController
                    // memblokir penarikan saat jumlah > saldo, sehingga saldo
                    // negatif tidak bisa ditarik. Admin tetap diberi peringatan
                    // untuk rekonsiliasi manual bila perlu.
                    $recipient->decrement('balance', $reverseAmount);

                    if ($reverseAmount > $currentBalance) {
                        $deficit = $reverseAmount - $currentBalance;
                        $warnings[] = 'Komisi Rp ' . number_format($reverseAmount, 0, ',', '.')
                            . ' dari "' . $recipient->name . '" ditarik kembali, tetapi saldonya saat itu hanya Rp '
                            . number_format($currentBalance, 0, ',', '.')
                            . ' (kemungkinan sudah ditarik). Saldo penerima kini minus Rp '
                            . number_format($deficit, 0, ',', '.')
                            . ' (mewakili hutang ke platform) dan perlu direkonsiliasi manual.';
                    }
                }
                $commission->delete();
            }

            // 2 & 3. Update atribusi di order.
            $order->update([
                'affiliate_id' => $resolvedAffiliateId,
                'upline_id' => $resolvedUplineId,
            ]);

            // 4. Re-create komisi hanya kalau order sudah lunas.
            if ($order->status === 'paid') {
                $this->processAffiliateCommissions($order->fresh());
            }

            return $warnings;
        });
    }

    private function processCommissions(Order $order): void
    {
        $product = $order->product;

        if (! $product) {
            return;
        }

        // Produk gratis (harga 0) tidak menghasilkan komisi
        if ($order->amount <= 0) {
            return;
        }

        $this->processAffiliateCommissions($order);

        // Pembuat produk TIDAK BOLEH dapat affiliate/upline commission dari
        // produk yang dia upload sendiri — yang berhak adalah tim dia &
        // member lain. Pembuat dapat creator share saja (lihat block di bawah).
        if ($product->created_by && (float) $product->creator_share_percent > 0) {
            $creator = User::find($product->created_by);
            if ($creator) {
                $creatorPercent = (float) $product->creator_share_percent;
                $creatorAmount = $order->amount * ($creatorPercent / 100);

                Commission::create([
                    'user_id' => $creator->id,
                    'order_id' => $order->id,
                    'type' => 'creator',
                    'amount' => $creatorAmount,
                    'status' => 'approved',
                ]);

                $creator->increment('balance', $creatorAmount);
            }
        }
    }

    /**
     * Hitung & kreditkan komisi direct (affiliate) dan upline untuk order.
     *
     * Dipisah dari processCommissions() supaya bisa dipakai ulang saat admin
     * mengganti affiliator (reassignAffiliate) tanpa menyentuh komisi creator.
     *
     * Catatan tarif: tarif komisi (owner vs non-owner) ditentukan berdasarkan
     * status kepemilikan affiliate/upline PADA SAAT order dibuat
     * ($order->created_at), bukan saat komisi diproses, supaya affiliate yang
     * baru beli produknya setelah sale terjadi tidak "naik tarif" retroaktif.
     */
    private function processAffiliateCommissions(Order $order): void
    {
        $product = $order->product;

        if (! $product || $order->amount <= 0) {
            return;
        }

        // Pembuat produk TIDAK BOLEH dapat affiliate/upline commission dari
        // produk yang dia upload sendiri.
        $creatorId = $product->created_by ? (int) $product->created_by : null;

        $referenceTime = $order->created_at;

        if ($order->affiliate_id && (int) $order->affiliate_id !== $creatorId) {
            $affiliate = User::find($order->affiliate_id);
            $directPercent = $product->commissionPercentFor($affiliate, $referenceTime);
            $directCommission = $order->amount * ($directPercent / 100);

            Commission::create([
                'user_id' => $order->affiliate_id,
                'order_id' => $order->id,
                'type' => 'direct',
                'amount' => $directCommission,
                'status' => 'approved',
            ]);

            if ($affiliate) {
                $affiliate->increment('balance', $directCommission);
            }
        }

        if ($order->upline_id && (int) $order->upline_id !== $creatorId) {
            $upline = User::find($order->upline_id);
            $uplinePercent = $product->uplinePercentFor($upline, $referenceTime);
            $uplineCommission = $order->amount * ($uplinePercent / 100);

            Commission::create([
                'user_id' => $order->upline_id,
                'order_id' => $order->id,
                'type' => 'upline',
                'amount' => $uplineCommission,
                'status' => 'approved',
            ]);

            if ($upline) {
                $upline->increment('balance', $uplineCommission);
            }
        }
    }

    /**
     * Buat lisensi otomatis untuk pembuat produk saat produknya disetujui admin,
     * agar pembuat bisa langsung test/menggunakan lisensi produknya sendiri.
     */
    public function assignLicenseToCreator(Product $product): void
    {
        if (! $product->isSoftware()) {
            return;
        }

        if (! $product->created_by) {
            return;
        }

        // Jangan buat duplikat — pembuat produk sudah punya lisensi untuk produk ini.
        $existing = License::where('product_id', $product->id)
            ->where('user_id', $product->created_by)
            ->exists();

        if ($existing) {
            return;
        }

        $key = $this->generateUniqueLicenseKey($product->id);
        $duration = $product->license_duration ?? 'lifetime';
        $expiresAt = $this->calculateExpiresAt($duration);

        License::create([
            'product_id' => $product->id,
            'key' => $key,
            'user_id' => $product->created_by,
            'assigned_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    private function assignLicense(Order $order): void
    {
        $product = $order->product;

        if (! $product || ! $product->isSoftware()) {
            return;
        }

        if ($order->license) {
            return;
        }

        $key = $this->generateUniqueLicenseKey($product->id);

        // Kalau order pakai paket harga, durasi mengikuti paket. Kalau tidak,
        // fallback ke license_duration default produk.
        $duration = $order->package?->duration_type ?? ($product->license_duration ?? 'lifetime');
        $expiresAt = $this->calculateExpiresAt($duration);

        License::create([
            'product_id' => $product->id,
            'key' => $key,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'assigned_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    private function calculateExpiresAt(string $duration): ?Carbon
    {
        return match ($duration) {
            '1_month' => now()->addMonth(),
            '6_months' => now()->addMonths(6),
            '1_year' => now()->addYear(),
            default => null,
        };
    }

    private function generateUniqueLicenseKey(int $productId): string
    {
        do {
            $key = strtoupper(
                Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4)
            );
        } while (License::where('product_id', $productId)->where('key', $key)->exists());

        return $key;
    }
}
