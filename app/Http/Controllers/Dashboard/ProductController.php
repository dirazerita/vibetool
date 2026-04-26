<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('landingPage')->where('is_active', true)->get();
        $user = $request->user();
        $downlines = $user->downlines()->select('id', 'name')->get();

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

        return view('dashboard.products', compact('products', 'user', 'downlines', 'promoProducts'));
    }
}
