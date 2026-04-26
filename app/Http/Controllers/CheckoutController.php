<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with('landingPage')->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $autoCouponCode = session('auto_coupon');
        $autoCouponMemberName = session('auto_coupon_member_name');
        $autoCouponData = null;

        if ($autoCouponCode) {
            $coupon = Coupon::where('code', $autoCouponCode)->first();
            $user = auth()->user();

            if ($coupon && $user && $coupon->isValidForUser($user) && $coupon->isValidForProduct($product) && $product->price >= $coupon->min_purchase) {
                $discount = $coupon->calculateDiscount($product->price);
                $autoCouponData = [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'member_name' => $autoCouponMemberName,
                    'discount' => $discount,
                    'discount_formatted' => 'Rp ' . number_format($discount, 0, ',', '.'),
                    'final_price' => $product->price - $discount,
                    'final_price_formatted' => 'Rp ' . number_format($product->price - $discount, 0, ',', '.'),
                ];
            }

            session()->forget(['auto_coupon', 'auto_coupon_member_name', 'intended_product_slug']);
        }

        return view('checkout', compact('product', 'autoCouponData'));
    }

    public function applyCoupon(Request $request, string $slug)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();
        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Kode kupon tidak ditemukan.']);
        }

        if (!$coupon->isValidForUser($user)) {
            return response()->json(['success' => false, 'message' => 'Kupon tidak valid untuk akun Anda.']);
        }

        if (!$coupon->isValidForProduct($product)) {
            return response()->json(['success' => false, 'message' => 'Kupon tidak berlaku untuk produk ini.']);
        }

        if ($product->price < $coupon->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal pembelian Rp ' . number_format($coupon->min_purchase, 0, ',', '.') . ' untuk menggunakan kupon ini.',
            ]);
        }

        $discount = $coupon->calculateDiscount($product->price);
        $finalPrice = $product->price - $discount;

        return response()->json([
            'success' => true,
            'message' => 'Kupon berhasil diterapkan!',
            'discount' => $discount,
            'discount_formatted' => 'Rp ' . number_format($discount, 0, ',', '.'),
            'final_price' => $finalPrice,
            'final_price_formatted' => 'Rp ' . number_format($finalPrice, 0, ',', '.'),
            'coupon_name' => $coupon->name,
        ]);
    }

    public function process(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();

        $affiliateId = null;
        $uplineId = null;
        $refCode = $request->cookie('ref');

        if ($refCode) {
            $affiliate = User::where('referral_code', $refCode)->first();
            if ($affiliate && $affiliate->id !== $user->id) {
                $affiliateId = $affiliate->id;
                $uplineId = $affiliate->upline_id;
            }
        }

        $amount = $product->price;
        $couponCode = null;
        $discountAmount = 0;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

            if ($coupon && $coupon->isValidForUser($user) && $coupon->isValidForProduct($product)) {
                $discountAmount = $coupon->calculateDiscount($product->price);
                $amount = $product->price - $discountAmount;
                $couponCode = $coupon->code;

                $coupon->increment('used_count');
            }
        }

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'affiliate_id' => $affiliateId,
            'upline_id' => $uplineId,
            'amount' => $amount,
            'coupon_code' => $couponCode,
            'discount_amount' => $discountAmount,
            'status' => 'pending',
            'download_token' => Str::uuid()->toString(),
        ]);

        $xendit = new XenditService();
        $invoice = $xendit->createInvoice([
            'external_id' => 'ORDER-' . $order->id,
            'amount' => $amount,
            'payer_email' => $user->email,
            'description' => 'Pembelian: ' . $product->title,
            'success_redirect_url' => route('checkout.success', $order->id),
            'failure_redirect_url' => route('product.show', $product->slug),
        ]);

        if (isset($invoice['invoice_url'])) {
            $order->update(['xendit_id' => $invoice['id']]);
            return redirect($invoice['invoice_url']);
        }

        return back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi.');
    }

    public function success(Order $order)
    {
        return view('checkout-success', compact('order'));
    }
}
