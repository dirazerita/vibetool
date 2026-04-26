<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function xendit(Request $request)
    {
        $webhookToken = config('services.xendit.webhook_token');
        if ($webhookToken && $request->header('x-callback-token') !== $webhookToken) {
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $externalId = $request->input('external_id');
        $status = $request->input('status');

        if (!$externalId || !str_starts_with($externalId, 'ORDER-')) {
            return response()->json(['message' => 'Invalid external_id'], 400);
        }

        $orderId = (int) str_replace('ORDER-', '', $externalId);
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($status === 'PAID') {
            $order->update(['status' => 'paid']);
            $this->processCommissions($order);
        } elseif ($status === 'EXPIRED') {
            $order->update(['status' => 'expired']);
        }

        return response()->json(['message' => 'OK']);
    }

    private function processCommissions(Order $order): void
    {
        $product = $order->product;

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
            $affiliate->increment('balance', $directCommission);
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
            $upline->increment('balance', $uplineCommission);
        }
    }
}
