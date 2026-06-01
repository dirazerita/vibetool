<?php

namespace App\Http\Controllers;

use App\Helpers\ImageResizer;
use App\Helpers\WhatsApp;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $product = Product::with(['landingPage', 'activePackages'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();
        $referrer = $this->resolveReferrer($request, $user);

        if ($user && $product->created_by && (int) $product->created_by === (int) $user->id) {
            return redirect()
                ->route('product.show', $product->slug)
                ->with('error', 'Anda tidak bisa membeli produk yang Anda upload sendiri. Produk ini sudah otomatis menjadi milik Anda.');
        }

        $selectedPackage = null;
        $packageId = (int) $request->input('package_id', 0);
        if ($packageId > 0) {
            $selectedPackage = $product->activePackages->firstWhere('id', $packageId);
        }
        // Kalau produk punya paket tapi user belum pilih, default ke paket pertama.
        if (! $selectedPackage && $product->activePackages->isNotEmpty()) {
            $selectedPackage = $product->activePackages->first();
        }

        Log::info('DEBUG checkout', [
            'user_id' => $user?->id,
            'upline_id' => $user?->upline_id,
            'product_slug' => $slug,
            'session_ref' => session('ref_code'),
            'session_auto_coupon' => session('auto_coupon'),
            'session_auto_coupon_member_id' => session('auto_coupon_member_id'),
            'session_auto_coupon_member_name' => session('auto_coupon_member_name'),
            'cookie_ref' => $request->cookie('ref'),
            'resolved_referrer_id' => $referrer?->id,
            'all_session' => session()->all(),
        ]);

        $this->ensureAutoCouponSession($product, $user, $referrer);

        $basePriceForCheckout = $selectedPackage ? (float) $selectedPackage->price : (float) $product->price;
        $autoCouponData = $this->buildAutoCouponData($product, $user, $referrer, $basePriceForCheckout);

        $alreadyPurchased = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'paid')
            ->exists();

        $pendingOrder = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->where('payment_method', 'manual')
            ->latest()
            ->first();

        return view('checkout', compact('product', 'autoCouponData', 'alreadyPurchased', 'pendingOrder', 'selectedPackage'));
    }

    public function applyCoupon(Request $request, string $slug)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();
        $referrer = $this->resolveReferrer($request, $user);
        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Kode kupon tidak ditemukan.']);
        }

        if (! $this->isCouponAccessible($coupon, $user, $referrer)) {
            Log::info('DEBUG checkout applyCoupon rejected', [
                'user_id' => $user?->id,
                'upline_id' => $user?->upline_id,
                'coupon_code' => $coupon->code,
                'session_auto_coupon' => session('auto_coupon'),
                'session_ref' => session('ref_code'),
                'resolved_referrer_id' => $referrer?->id,
            ]);

            return response()->json(['success' => false, 'message' => 'Kupon tidak valid untuk akun Anda.']);
        }

        if (! $coupon->isValidForProduct($product)) {
            return response()->json(['success' => false, 'message' => 'Kupon tidak berlaku untuk produk ini.']);
        }

        if ($product->price < $coupon->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal pembelian Rp '.number_format($coupon->min_purchase, 0, ',', '.').' untuk menggunakan kupon ini.',
            ]);
        }

        $discount = $coupon->calculateDiscount($product->price);
        $finalPrice = $product->price - $discount;

        return response()->json([
            'success' => true,
            'message' => 'Kupon berhasil diterapkan!',
            'discount' => $discount,
            'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
            'final_price' => $finalPrice,
            'final_price_formatted' => 'Rp '.number_format($finalPrice, 0, ',', '.'),
            'coupon_name' => $coupon->name,
        ]);
    }

    public function process(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();
        $referrer = $this->resolveReferrer($request, $user);

        if ($user && $product->created_by && (int) $product->created_by === (int) $user->id) {
            return redirect()
                ->route('product.show', $product->slug)
                ->with('error', 'Anda tidak bisa membeli produk yang Anda upload sendiri.');
        }

        // Resolve package kalau produk punya paket aktif.
        $selectedPackage = null;
        $product->load('activePackages');
        if ($product->activePackages->isNotEmpty()) {
            $packageId = (int) $request->input('package_id', 0);
            if ($packageId > 0) {
                $selectedPackage = $product->activePackages->firstWhere('id', $packageId);
            }
            if (! $selectedPackage) {
                $selectedPackage = $product->activePackages->first();
            }
        }

        $basePrice = $selectedPackage ? (float) $selectedPackage->price : (float) $product->price;

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
        // Kalau ada referral yang nge-resolve ke pembuat → abaikan (anggap no ref).
        if ($product->created_by) {
            $creatorId = (int) $product->created_by;
            if ($affiliateId === $creatorId) {
                $affiliateId = null;
            }
            if ($uplineId === $creatorId) {
                $uplineId = null;
            }
        }

        $amount = $basePrice;
        $couponCode = null;
        $discountAmount = 0;

        $couponInput = $request->input('coupon_code') ?: session('auto_coupon');
        if ($couponInput) {
            $coupon = Coupon::where('code', strtoupper($couponInput))->first();

            if ($coupon
                && $this->isCouponAccessible($coupon, $user, $referrer)
                && $coupon->isValidForProduct($product)
                && $basePrice >= $coupon->min_purchase
            ) {
                $discountAmount = $coupon->calculateDiscount($basePrice);
                $amount = $basePrice - $discountAmount;
                $couponCode = $coupon->code;

                $coupon->increment('used_count');
            }
        }

        $manualEnabled = Setting::get('manual_payment_enabled') === '1';

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'package_id' => $selectedPackage?->id,
            'affiliate_id' => $affiliateId,
            'upline_id' => $uplineId,
            'amount' => $amount,
            'coupon_code' => $couponCode,
            'discount_amount' => $discountAmount,
            'status' => 'pending',
            'payment_method' => $manualEnabled ? 'manual' : 'xendit',
            'download_token' => Str::uuid()->toString(),
        ]);

        if ($manualEnabled) {
            session()->forget(['auto_coupon', 'auto_coupon_member_name', 'auto_coupon_member_id', 'intended_product_slug', 'ref_code']);

            try {
                app(TelegramService::class)->notifyNewOrder($order->fresh()->load(['user', 'product', 'affiliate']));
            } catch (\Throwable $e) {
                Log::warning('Telegram notify new order failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return redirect()->route('checkout.manual', $order->id);
        }

        $xendit = new XenditService;
        $invoice = $xendit->createInvoice([
            'external_id' => 'ORDER-'.$order->id,
            'amount' => $amount,
            'payer_email' => $user->email,
            'description' => 'Pembelian: '.$product->title,
            'success_redirect_url' => route('checkout.success', $order->id),
            'failure_redirect_url' => route('product.show', $product->slug),
        ]);

        if (isset($invoice['invoice_url'])) {
            $order->update(['xendit_id' => $invoice['id']]);

            session()->forget(['auto_coupon', 'auto_coupon_member_name', 'auto_coupon_member_id', 'intended_product_slug', 'ref_code']);

            return redirect($invoice['invoice_url']);
        }

        Log::error('CheckoutController::process invoice tidak ter-create', [
            'order_id' => $order->id,
            'amount' => $amount,
            'xendit_response' => $invoice,
        ]);

        $errorMessage = 'Gagal membuat invoice pembayaran. Silakan coba lagi.';
        if (($invoice['error_code'] ?? null) === 'XENDIT_SECRET_KEY_MISSING') {
            $errorMessage = 'Konfigurasi pembayaran belum lengkap. Hubungi admin (XENDIT_SECRET_KEY belum di-set).';
        } elseif (isset($invoice['message'])) {
            $errorMessage = 'Gagal membuat invoice pembayaran: '.$invoice['message'];
        }

        return back()->with('error', $errorMessage);
    }

    public function success(Order $order)
    {
        return view('checkout-success', compact('order'));
    }

    public function manual(Request $request, Order $order)
    {
        $this->authorizeOrderOwner($request, $order);

        if ($order->payment_method !== 'manual') {
            return redirect()->route('checkout.success', $order->id);
        }

        $bankInfo = [
            'bank_name' => Setting::get('manual_bank_name', ''),
            'bank_account' => Setting::get('manual_bank_account', ''),
            'bank_holder' => Setting::get('manual_bank_holder', ''),
            'note' => Setting::get('manual_payment_note', ''),
        ];

        $order->loadMissing(['product', 'user']);
        $adminWhatsappLink = WhatsApp::manualPaymentLink($order);

        return view('checkout-manual', compact('order', 'bankInfo', 'adminWhatsappLink'));
    }

    public function uploadProof(Request $request, Order $order)
    {
        $this->authorizeOrderOwner($request, $order);

        if ($order->payment_method !== 'manual') {
            abort(404);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('checkout.manual', $order->id)
                ->with('error', 'Pesanan ini sudah tidak dalam status menunggu pembayaran.');
        }

        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'proof.required' => 'Bukti transfer wajib diupload.',
            'proof.image' => 'File harus berupa gambar.',
            'proof.mimes' => 'Format yang diterima: JPG, JPEG, PNG, WEBP.',
            'proof.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = ImageResizer::resizeProof($request->file('proof'), 'payment_proofs/'.$order->id);
        $order->update(['payment_proof' => $path]);

        try {
            app(TelegramService::class)->notifyPaymentProof($order->fresh()->load(['user', 'product', 'affiliate']));
        } catch (\Throwable $e) {
            Log::warning('Telegram notify payment proof failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('checkout.manual', $order->id)
            ->with('success', 'Bukti transfer berhasil diupload. Menunggu konfirmasi admin.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOrderOwner($request, $order);

        if ($order->status !== 'pending') {
            return redirect()->route('dashboard.purchases')
                ->with('error', 'Pesanan ini tidak bisa dibatalkan karena bukan dalam status menunggu pembayaran.');
        }

        if ($order->payment_method !== 'manual') {
            return redirect()->route('dashboard.purchases')
                ->with('error', 'Pesanan ini tidak bisa dibatalkan dari sini.');
        }

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        if ($order->coupon_code) {
            $coupon = Coupon::where('code', $order->coupon_code)->first();
            if ($coupon && $coupon->used_count > 0) {
                $coupon->decrement('used_count');
            }
        }

        $order->update([
            'status' => 'cancelled',
            'payment_proof' => null,
        ]);

        return redirect()->route('dashboard.purchases')
            ->with('success', 'Pesanan #'.$order->id.' berhasil dibatalkan.');
    }

    private function authorizeOrderOwner(Request $request, Order $order): void
    {
        if (! $request->user() || $request->user()->id !== $order->user_id) {
            abort(403);
        }
    }

    private function resolveReferrer(Request $request, ?User $user): ?User
    {
        $refCode = session('ref_code') ?? $request->cookie('ref');

        if ($refCode) {
            $referrer = User::where('referral_code', $refCode)->first();
            if ($referrer && (! $user || $referrer->id !== $user->id)) {
                return $referrer;
            }
        }

        if ($user && $user->upline_id) {
            $upline = User::find($user->upline_id);
            if ($upline) {
                return $upline;
            }
        }

        $autoCouponMemberId = session('auto_coupon_member_id');
        if ($autoCouponMemberId) {
            $autoMember = User::find($autoCouponMemberId);
            if ($autoMember && (! $user || $autoMember->id !== $user->id)) {
                return $autoMember;
            }
        }

        return null;
    }

    private function isCouponAccessible(Coupon $coupon, ?User $user, ?User $referrer): bool
    {
        if (! $user) {
            return false;
        }

        $sessionAuto = session('auto_coupon');
        if ($sessionAuto && strtoupper($sessionAuto) === strtoupper($coupon->code)) {
            return $coupon->is_active
                && (! $coupon->expired_at || ! $coupon->expired_at->isPast())
                && (! $coupon->max_uses || $coupon->used_count < $coupon->max_uses);
        }

        return $coupon->isAccessibleBy($user, $referrer);
    }

    private function ensureAutoCouponSession(Product $product, ?User $user, ?User $referrer): void
    {
        if (session('auto_coupon')) {
            return;
        }

        if ($user) {
            $ownedCoupon = $user->coupons()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
                })
                ->where(function ($query) {
                    $query->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
                })
                ->get()
                ->filter(fn ($c) => $c->isValidForProduct($product) && $product->price >= $c->min_purchase)
                ->sortByDesc(fn ($c) => $c->calculateDiscount($product->price))
                ->first();

            if ($ownedCoupon) {
                session([
                    'auto_coupon' => $ownedCoupon->code,
                    'auto_coupon_member_name' => null,
                ]);
                Log::info('Checkout auto_coupon recovered from buyer-owned coupon', [
                    'user_id' => $user->id,
                    'coupon_code' => $ownedCoupon->code,
                ]);

                return;
            }
        }

        if (! $referrer) {
            return;
        }

        $coupon = $referrer->coupons()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->get()
            ->first(fn ($c) => $c->isValidForProduct($product) && $product->price >= $c->min_purchase);

        if ($coupon) {
            session([
                'auto_coupon' => $coupon->code,
                'auto_coupon_member_name' => $referrer->name,
            ]);
            Log::info('Checkout auto_coupon recovered from referrer', [
                'referrer_id' => $referrer->id,
                'coupon_code' => $coupon->code,
            ]);
        }
    }

    private function buildAutoCouponData(Product $product, ?User $user, ?User $referrer, ?float $basePriceOverride = null): ?array
    {
        $autoCouponCode = session('auto_coupon');
        if (! $autoCouponCode || ! $user) {
            return null;
        }

        $basePrice = $basePriceOverride ?? (float) $product->price;

        $coupon = Coupon::where('code', $autoCouponCode)->first();
        if (! $coupon
            || ! $this->isCouponAccessible($coupon, $user, $referrer)
            || ! $coupon->isValidForProduct($product)
            || $basePrice < $coupon->min_purchase
        ) {
            Log::info('DEBUG checkout buildAutoCouponData rejected', [
                'user_id' => $user?->id,
                'auto_coupon_code' => $autoCouponCode,
                'coupon_found' => (bool) $coupon,
                'is_accessible' => $coupon ? $this->isCouponAccessible($coupon, $user, $referrer) : null,
                'is_valid_for_product' => $coupon ? $coupon->isValidForProduct($product) : null,
                'min_purchase_ok' => $coupon ? ($basePrice >= $coupon->min_purchase) : null,
            ]);

            return null;
        }

        $discount = $coupon->calculateDiscount($basePrice);
        $discountLabel = $coupon->discount_type === 'percent'
            ? rtrim(rtrim(number_format($coupon->discount_value, 2, ',', '.'), '0'), ',').'%'
            : 'Rp '.number_format($coupon->discount_value, 0, ',', '.');

        return [
            'code' => $coupon->code,
            'name' => $coupon->name,
            'member_name' => session('auto_coupon_member_name'),
            'discount' => $discount,
            'discount_formatted' => 'Rp '.number_format($discount, 0, ',', '.'),
            'discount_label' => $discountLabel,
            'final_price' => $basePrice - $discount,
            'final_price_formatted' => 'Rp '.number_format($basePrice - $discount, 0, ',', '.'),
        ];
    }
}
