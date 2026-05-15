<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalCommissions = $user->commissions()->sum('amount');
        $pendingCommissions = $user->commissions()->where('status', 'pending')->sum('amount');
        $totalDownlines = $user->downlines()->count();
        $totalOrders = $user->affiliateOrders()->where('status', 'paid')->count();

        $welcomeModal = null;
        if ($request->session()->pull('show_welcome_modal', false)) {
            $pendingManualOrder = $user->orders()
                ->with('product')
                ->where('status', 'pending')
                ->where('payment_method', 'manual')
                ->latest()
                ->first();

            if ($pendingManualOrder && $pendingManualOrder->product) {
                $welcomeModal = [
                    'type' => 'pending_order',
                    'product' => $pendingManualOrder->product,
                    'order' => $pendingManualOrder,
                    'cta_url' => route('checkout.manual', $pendingManualOrder),
                    'cta_label' => $pendingManualOrder->payment_proof ? 'Lihat Status Pembayaran' : 'Lanjutkan Pembayaran',
                ];
            } else {
                $intendedProduct = $user->intendedProduct;
                if ($intendedProduct && $intendedProduct->is_active) {
                    $hasOrderForIntended = $user->orders()
                        ->where('product_id', $intendedProduct->id)
                        ->whereIn('status', ['paid', 'pending'])
                        ->exists();

                    if (!$hasOrderForIntended) {
                        $welcomeModal = [
                            'type' => 'intended_product',
                            'product' => $intendedProduct,
                            'cta_url' => route('checkout', $intendedProduct->slug),
                            'cta_label' => 'Beli Sekarang',
                        ];
                    }
                }
            }
        }

        return view('dashboard.index', compact('user', 'totalCommissions', 'pendingCommissions', 'totalDownlines', 'totalOrders', 'welcomeModal'));
    }
}
