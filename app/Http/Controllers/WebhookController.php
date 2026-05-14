<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function xendit(Request $request, OrderPaymentService $paymentService)
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
            $paymentService->markAsPaid($order);
        } elseif ($status === 'EXPIRED') {
            $paymentService->markAsExpired($order);
        }

        return response()->json(['message' => 'OK']);
    }
}
