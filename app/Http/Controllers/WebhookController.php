<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderPaymentService;
use App\Services\PakasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function pakasir(Request $request, OrderPaymentService $paymentService)
    {
        $orderId = $request->input('order_id');
        $status = $request->input('status');
        $amount = (int) $request->input('amount');
        $project = $request->input('project');

        Log::info('Pakasir webhook received', [
            'order_id' => $orderId,
            'status' => $status,
            'amount' => $amount,
            'project' => $project,
        ]);

        $expectedSlug = Setting::get('pakasir_slug', '');
        if ($expectedSlug !== '' && $project !== $expectedSlug) {
            return response()->json(['message' => 'Invalid project'], 403);
        }

        if (!$orderId || !str_starts_with((string) $orderId, 'ORDER-')) {
            return response()->json(['message' => 'Invalid order_id'], 400);
        }

        $id = (int) str_replace('ORDER-', '', $orderId);
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ((int) $order->amount !== $amount) {
            Log::warning('Pakasir webhook amount mismatch', [
                'order_id' => $id,
                'expected' => $order->amount,
                'received' => $amount,
            ]);
        }

        if ($status === 'completed') {
            $pakasir = new PakasirService();
            $detail = $pakasir->verifyTransaction($orderId, $amount);
            $verified = data_get($detail, 'transaction.status') === 'completed';

            if ($verified) {
                $paymentService->markAsPaid($order);
            } else {
                Log::warning('Pakasir webhook: verification failed, marking paid anyway', [
                    'order_id' => $id,
                    'detail' => $detail,
                ]);
                $paymentService->markAsPaid($order);
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
