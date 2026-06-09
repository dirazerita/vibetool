<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalCommissions = $user->commissions()->sum('amount');
        $pendingCommissions = $user->commissions()->where('status', 'pending')->sum('amount');
        $totalDownlines = $user->downlines()->count();

        // Total Penjualan = paid orders dengan amount > 0 (produk gratis tidak dihitung)
        // dimana user adalah affiliate (L1) atau upline (L2). Untuk member yang bisa
        // upload produk, tambahkan juga penjualan produk milik mereka di luar downline
        // (dibeli orang lain yang bukan downline-nya).
        $totalOrders = $this->salesQuery($user)->count();

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

                    if (! $hasOrderForIntended) {
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

    /**
     * Query gabungan untuk "Total Penjualan" — order paid + amount > 0 yang
     * dianggap penjualan user. Dipakai juga di SalesController untuk konsistensi.
     */
    public static function salesQuery(User $user)
    {
        return Order::query()
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->where(function ($q) use ($user) {
                $q->where('affiliate_id', $user->id)
                    ->orWhere('upline_id', $user->id);

                if ($user->canUploadProduct()) {
                    $ownProductIds = $user->createdProducts()->pluck('id');
                    if ($ownProductIds->isNotEmpty()) {
                        $q->orWhere(function ($q2) use ($user, $ownProductIds) {
                            $q2->whereIn('product_id', $ownProductIds)
                                ->where(function ($q3) use ($user) {
                                    $q3->whereNull('affiliate_id')
                                        ->orWhere('affiliate_id', '!=', $user->id);
                                })
                                ->where(function ($q3) use ($user) {
                                    $q3->whereNull('upline_id')
                                        ->orWhere('upline_id', '!=', $user->id);
                                });
                        });
                    }
                }
            });
    }
}
