<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderPaymentService
{
    public function markAsPaid(Order $order): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'paid']);
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

        if (!$product) {
            return;
        }

        if ($order->affiliate_id) {
            $affiliate = User::find($order->affiliate_id);
            $directPercent = $product->commissionPercentFor($affiliate);
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

        if ($order->upline_id) {
            $upline = User::find($order->upline_id);
            $uplinePercent = $product->uplinePercentFor($upline);
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

    private function assignLicense(Order $order): void
    {
        $product = $order->product;

        if (!$product || !$product->isSoftware()) {
            return;
        }

        if ($order->license) {
            return;
        }

        $key = $this->generateUniqueLicenseKey($product->id);

        License::create([
            'product_id' => $product->id,
            'key' => $key,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'assigned_at' => now(),
        ]);
    }

    private function generateUniqueLicenseKey(int $productId): string
    {
        do {
            $key = strtoupper(
                Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
            );
        } while (License::where('product_id', $productId)->where('key', $key)->exists());

        return $key;
    }
}
