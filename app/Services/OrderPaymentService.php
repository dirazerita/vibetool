<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\License;
use App\Models\Order;
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

    private function processCommissions(Order $order): void
    {
        $product = $order->product;

        if (! $product) {
            return;
        }

        // Pembuat produk TIDAK BOLEH dapat affiliate/upline commission dari
        // produk yang dia upload sendiri — yang berhak adalah tim dia &
        // member lain. Pembuat dapat creator share saja (lihat block di bawah).
        $creatorId = $product->created_by ? (int) $product->created_by : null;

        // PENTING: tarif komisi (owner vs non-owner) ditentukan berdasarkan
        // status kepemilikan affiliate/upline PADA SAAT order ini dibuat
        // ($order->created_at), bukan saat commission diproses. Ini supaya
        // affiliate yang baru beli produknya setelah sale terjadi tidak
        // "naik tarif" secara retroaktif.
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
