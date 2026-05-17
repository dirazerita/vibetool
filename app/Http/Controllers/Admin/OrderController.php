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

        $activatedMember = false;

        if ($order->user && $order->user->status !== 'active') {
            $order->user->update(['status' => 'active']);
            $order->setRelation('user', $order->user->fresh());
            $activatedMember = true;
        }

        $paymentService->markAsPaid($order);

        $message = 'Pesanan #' . $order->id . ' berhasil ditandai lunas. Komisi sudah diproses.';

        if ($activatedMember && $order->user) {
            $message = 'Pesanan #' . $order->id . ' berhasil ditandai lunas dan member "' . $order->user->name . '" otomatis diaktifkan. Komisi sudah diproses.';
        }

        return back()->with('success', $message);
    }
}
