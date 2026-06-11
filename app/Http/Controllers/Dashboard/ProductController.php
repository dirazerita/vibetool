<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MemberCommission;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['landingPage', 'activePackages'])->where('is_active', true)->get();
        $user = $request->user()->load('upline');
        $downlines = $user->downlines()->select('id', 'name')->get();

        // Komisi khusus per-produk yang di-set admin untuk member ini.
        // Dipreload sekali (keyed by product_id) supaya view bisa menampilkan
        // tarif khusus tanpa N+1 query, dan tampilannya konsisten dengan tarif
        // yang benar-benar dibayar di OrderPaymentService.
        $memberCommissions = MemberCommission::where('user_id', $user->id)
            ->get()
            ->keyBy('product_id');

        $userOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'pending'])
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhere(function ($qq) {
                        $qq->where('status', 'pending')->where('payment_method', 'manual');
                    });
            })
            ->orderByDesc('id')
            ->get();

        $purchaseStatus = [];
        foreach ($userOrders as $order) {
            $pid = $order->product_id;
            if (! isset($purchaseStatus[$pid])) {
                $purchaseStatus[$pid] = [
                    'paid' => $order->status === 'paid',
                    'pending_order' => $order->status === 'pending' ? $order : null,
                ];
            } else {
                if ($order->status === 'paid') {
                    $purchaseStatus[$pid]['paid'] = true;
                } elseif (! $purchaseStatus[$pid]['pending_order'] && $order->status === 'pending') {
                    $purchaseStatus[$pid]['pending_order'] = $order;
                }
            }
        }

        $memberCoupons = $user->coupons()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->with('products')
            ->get();

        $promoProducts = [];
        foreach ($products as $product) {
            foreach ($memberCoupons as $coupon) {
                if ($coupon->isValidForProduct($product)) {
                    $promoProducts[$product->id] = [
                        'product' => $product,
                        'coupon' => $coupon,
                    ];
                    break;
                }
            }
        }

        return view('dashboard.products', compact('products', 'user', 'downlines', 'promoProducts', 'purchaseStatus', 'memberCommissions'));
    }
}
