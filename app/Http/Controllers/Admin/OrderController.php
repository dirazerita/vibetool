<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'product', 'affiliate'])
            ->latest()
            ->paginate(15);

        return view('admin.orders', compact('orders'));
    }

    public function markPaid(Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu pembayaran.');
        }

        $paymentService->markAsPaid($order);

        return back()->with('success', 'Pesanan #' . $order->id . ' berhasil ditandai lunas. Komisi sudah diproses.');
    }
}
