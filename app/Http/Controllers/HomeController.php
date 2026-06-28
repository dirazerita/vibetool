<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with(['landingPage', 'activePackages'])->where('is_active', true)->where('approval_status', 'approved')->latest()->get();

        return view('home', compact('products'));
    }

    public function show(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->where('approval_status', 'approved')->firstOrFail();

        // Simpan intended product slug untuk redirect setelah register,
        // baik dengan ref maupun tanpa ref.
        session(['intended_product_slug' => $slug]);

        $refCode = $request->query('ref');
        if ($refCode) {
            $refMember = User::where('referral_code', $refCode)->first();
            if ($refMember) {
                session(['ref_code' => $refCode]);
                session(['intended_product_slug' => $slug]);

                $autoCoupon = $this->findAutoCoupon($refMember, $product);
                if ($autoCoupon) {
                    session([
                        'auto_coupon' => $autoCoupon->code,
                        'auto_coupon_member_name' => $refMember->name,
                        'auto_coupon_member_id' => $refMember->id,
                    ]);
                } else {
                    session()->forget(['auto_coupon', 'auto_coupon_member_name', 'auto_coupon_member_id']);
                }
            }
        }

        $landingPage = $product->landingPage;

        if ($landingPage && $landingPage->is_published) {
            // Full HTML Page — ganti seluruh landing page dengan HTML kustom user
            if ($landingPage->use_full_html && $landingPage->full_html) {
                return response($landingPage->full_html);
            }

            $product->load([
                'landingPageImages',
                'landingPageTestimonials' => function ($query) {
                    $query->where('is_active', true);
                },
                'activePackages',
            ]);

            return view('product.landing', compact('product', 'landingPage'));
        }

        $product->load('activePackages');

        return view('product.show', compact('product'));
    }

    private function findAutoCoupon(User $member, Product $product): ?Coupon
    {
        $coupons = $member->coupons()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->get();

        foreach ($coupons as $coupon) {
            if ($coupon->isValidForProduct($product)) {
                return $coupon;
            }
        }

        return null;
    }
}
