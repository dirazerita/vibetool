<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'product', 'affiliate', 'uplineUser'])
            ->latest()
            ->paginate(15);

        // Daftar member (non-admin) untuk dropdown pemilihan affiliator.
        $members = User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'upline_id']);

        return view('admin.orders', compact('orders', 'members'));
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

    /**
     * Ganti affiliator sebuah pesanan. Upline-nya otomatis ikut diatur dari
     * upline_id affiliator yang dipilih, dan komisi dihitung ulang bila order
     * sudah lunas.
     */
    public function updateAffiliate(Request $request, Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'affiliate_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'affiliate_id.exists' => 'Member affiliator yang dipilih tidak ditemukan.',
        ]);

        $affiliateId = $validated['affiliate_id'] ?? null;

        if ($affiliateId && (int) $affiliateId === (int) $order->user_id) {
            return back()->with('error', 'Affiliator tidak boleh sama dengan pembeli.');
        }

        $product = $order->product;
        if ($affiliateId && $product && $product->created_by && (int) $affiliateId === (int) $product->created_by) {
            return back()->with('error', 'Pembuat produk tidak bisa menjadi affiliator untuk produknya sendiri.');
        }

        $paymentService->reassignAffiliate($order, $affiliateId ? (int) $affiliateId : null);

        $order->refresh()->load(['affiliate', 'uplineUser']);

        if ($order->affiliate_id) {
            $message = 'Affiliator pesanan #' . $order->id . ' berhasil diubah ke "' . ($order->affiliate->name ?? '-') . '".';
            if ($order->upline_id) {
                $message .= ' Upline "' . ($order->uplineUser->name ?? '-') . '" ikut di-set untuk bonus upline.';
            }
            if ($order->status === 'paid') {
                $message .= ' Komisi sudah dihitung ulang.';
            }
        } else {
            $message = 'Affiliator pesanan #' . $order->id . ' berhasil dihapus.'
                . ($order->status === 'paid' ? ' Komisi affiliator & upline sebelumnya sudah ditarik kembali.' : '');
        }

        return back()->with('success', $message);
    }
}
