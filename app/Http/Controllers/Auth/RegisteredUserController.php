<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $ref = $this->resolveRefCode($request);

        $refMemberName = null;
        if ($ref) {
            $refMember = User::where('referral_code', $ref)->first();
            if ($refMember) {
                $refMemberName = $refMember->name;
                session(['ref_code' => $ref]);
            }
        }

        return view('auth.register', compact('ref', 'refMemberName'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedWhatsapp = PhoneNumber::normalize($request->input('whatsapp_number'));
        $request->merge(['whatsapp_number' => $normalizedWhatsapp]);

        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'whatsapp_number' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('users', 'whatsapp_number'),
                ],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor yang berbeda.',
            ]
        );

        $uplineId = null;
        $refCode = $this->resolveRefCode($request);
        if ($refCode) {
            $upline = User::where('referral_code', $refCode)->first();
            if ($upline) {
                $uplineId = $upline->id;
            }
        }

        $intendedProductId = null;
        $intendedProduct = null;
        $intendedSlug = session('intended_product_slug');
        if ($intendedSlug) {
            $intendedProduct = Product::where('slug', $intendedSlug)->first();
            if ($intendedProduct) {
                $intendedProductId = $intendedProduct->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $normalizedWhatsapp,
            'password' => Hash::make($request->password),
            'upline_id' => $uplineId,
            'intended_product_id' => $intendedProductId,
            'status' => 'pending',
        ]);

        Log::info('DEBUG register stored', [
            'user_id' => $user->id,
            'upline_id' => $user->upline_id,
            'ref_code_used' => $refCode,
            'session_auto_coupon' => session('auto_coupon'),
            'session_ref_code' => session('ref_code'),
            'session_auto_coupon_member_id' => session('auto_coupon_member_id'),
        ]);

        event(new Registered($user));

        // Auto-create pending manual order kalau user daftar via link affiliate produk —
        // supaya order tersimpan menunggu pembayaran sejak sebelum admin aktivasi.
        if ($intendedProduct && $intendedProduct->is_active) {
            $this->createPendingOrderForRegistrant($user, $intendedProduct);
        }

        // Sistem aktivasi via WhatsApp: akun pending, tidak otomatis login.
        // Simpan data user untuk halaman pending lalu logout & redirect.
        $pendingData = [
            'name' => $user->name,
            'email' => $user->email,
            'whatsapp_number' => $user->whatsapp_number,
        ];

        if ($intendedProduct) {
            $pendingData['product_title'] = $intendedProduct->title;
            $pendingData['product_slug'] = $intendedProduct->slug;
            $pendingData['product_price'] = (float) $intendedProduct->price;
        }

        session(['pending_user_data' => $pendingData]);

        Auth::guard('web')->logout();

        return redirect()->route('pending');
    }

    private function createPendingOrderForRegistrant(User $user, Product $product): void
    {
        $affiliateId = $user->upline_id;
        $upline = $affiliateId ? User::find($affiliateId) : null;
        $uplineOfAffiliate = $upline?->upline_id;

        $amount = (float) $product->price;
        $couponCode = null;
        $discountAmount = 0;

        $autoCouponCode = session('auto_coupon');
        if ($autoCouponCode) {
            $coupon = Coupon::where('code', strtoupper($autoCouponCode))->first();

            if ($coupon
                && $coupon->isAccessibleBy($user, $upline)
                && $coupon->isValidForProduct($product)
                && $product->price >= $coupon->min_purchase
            ) {
                $discountAmount = $coupon->calculateDiscount($product->price);
                $amount = max(0, $product->price - $discountAmount);
                $couponCode = $coupon->code;
                $coupon->increment('used_count');
            }
        }

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'affiliate_id' => $affiliateId,
                'upline_id' => $uplineOfAffiliate,
                'amount' => $amount,
                'coupon_code' => $couponCode,
                'discount_amount' => $discountAmount,
                'status' => 'pending',
                'payment_method' => 'manual',
                'download_token' => Str::uuid()->toString(),
            ]);

            Log::info('Auto-created pending order on registration', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'affiliate_id' => $affiliateId,
                'amount' => $amount,
                'coupon_code' => $couponCode,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to auto-create pending order on registration', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveRefCode(Request $request): ?string
    {
        $candidates = [
            $request->input('ref'),
            $request->cookie('ref'),
            session('ref_code'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }
}
