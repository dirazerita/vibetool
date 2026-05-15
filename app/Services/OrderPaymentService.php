<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $directCommission = $order->amount * ($product->commission_percent / 100);
            Commission::create([
                'user_id' => $order->affiliate_id,
                'order_id' => $order->id,
                'type' => 'direct',
                'amount' => $directCommission,
                'status' => 'approved',
            ]);

            $affiliate = User::find($order->affiliate_id);
            if ($affiliate) {
                $affiliate->increment('balance', $directCommission);
            }
        }

        if ($order->upline_id) {
            $uplineCommission = $order->amount * ($product->upline_percent / 100);
            Commission::create([
                'user_id' => $order->upline_id,
                'order_id' => $order->id,
                'type' => 'upline',
                'amount' => $uplineCommission,
                'status' => 'approved',
            ]);

            $upline = User::find($order->upline_id);
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

        $license = License::where('product_id', $product->id)
            ->whereNull('order_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (!$license) {
            Log::warning('Stok lisensi habis saat pembayaran order.', [
                'order_id' => $order->id,
                'product_id' => $product->id,
            ]);
            return;
        }

        $license->update([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'assigned_at' => now(),
        ]);
    }
}
