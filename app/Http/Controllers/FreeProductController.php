<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FreeProductController extends Controller
{
    public function claim(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        if (! $product->isFree()) {
            return redirect()->route('checkout', $product->slug);
        }

        $user = $request->user();

        if ($product->created_by && (int) $product->created_by === (int) $user->id) {
            return redirect()->route('product.show', $product->slug)
                ->with('error', 'Anda tidak bisa klaim produk yang Anda upload sendiri. Produk ini sudah otomatis menjadi milik Anda.');
        }

        $existing = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'paid')
            ->first();

        if ($existing) {
            return redirect()->route('dashboard.purchases')
                ->with('info', 'Kamu sudah klaim produk ini sebelumnya.');
        }

        $affiliateId = null;
        $uplineId = null;
        $refCode = $request->cookie('ref') ?? session('ref_code');

        if ($refCode) {
            $affiliate = User::where('referral_code', $refCode)->first();
            if ($affiliate && $affiliate->id !== $user->id) {
                $affiliateId = $affiliate->id;
                $uplineId = $affiliate->upline_id;
            }
        }

        // Pembuat produk tidak boleh jadi affiliate/upline untuk produknya sendiri.
        if ($product->created_by) {
            $creatorId = (int) $product->created_by;
            if ($affiliateId === $creatorId) {
                $affiliateId = null;
            }
            if ($uplineId === $creatorId) {
                $uplineId = null;
            }
        }

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'affiliate_id' => $affiliateId,
            'upline_id' => $uplineId,
            'amount' => 0,
            'status' => 'pending',
            'payment_method' => 'free',
            'download_token' => Str::uuid()->toString(),
        ]);

        app(OrderPaymentService::class)->markAsPaid($order->fresh());

        session()->forget(['auto_coupon', 'auto_coupon_member_name', 'auto_coupon_member_id', 'intended_product_slug', 'ref_code']);

        try {
            app(TelegramService::class)->notifyFreeClaim($order->fresh()->load(['user', 'product', 'affiliate']));
        } catch (\Throwable $e) {
            Log::warning('Telegram notify free claim failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('dashboard.purchases')
            ->with('success', 'Produk gratis "'.$product->title.'" berhasil diklaim! Login ke softwarenya pakai email + password akun PRODIG kamu.');
    }
}
