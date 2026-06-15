<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresi: ketika downline checkout memakai kupon milik upline-nya TANPA klik
 * link ?ref=, komisi harus tetap diatribusikan ke pemilik kupon (affiliate) dan
 * upline pemilik kupon (bonus upline). Sebelumnya affiliate_id/upline_id null
 * sehingga OrderPaymentService tidak membuat baris komisi sama sekali.
 */
class CouponCommissionAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Aktifkan pembayaran manual supaya process() membuat order pending
        // tanpa memanggil gateway eksternal.
        Setting::set('manual_payment_enabled', '1');
    }

    private function makeMember(?int $uplineId = null): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'upline_id' => $uplineId,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'title' => 'Produk Uji',
            'slug' => 'produk-uji-'.Str::lower(Str::random(6)),
            'description' => 'desc',
            'price' => 100000,
            'commission_percent' => 30,
            'commission_percent_non_owner' => 30,
            'upline_percent' => 10,
            'upline_percent_non_owner' => 10,
            'creator_share_percent' => 0,
            'product_type' => 'digital',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    public function test_coupon_owner_gets_attributed_as_affiliate_without_ref_link(): void
    {
        // rynz2018 (upline) -> OmBags (affiliate/pemilik kupon) -> buyer (downline)
        $topUpline = $this->makeMember();
        $ombags = $this->makeMember($topUpline->id);
        $buyer = $this->makeMember($ombags->id);

        $product = $this->makeProduct();

        $coupon = Coupon::create([
            'code' => 'OMBAGS-'.Str::upper(Str::random(4)),
            'name' => 'Kupon OmBags',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
        ]);
        $ombags->coupons()->attach($coupon->id);

        // Checkout TANPA cookie/ref link, hanya pakai kupon milik upline.
        $this->actingAs($buyer)
            ->post('/checkout/'.$product->slug, [
                'coupon_code' => $coupon->code,
            ])
            ->assertRedirect();

        $order = Order::where('user_id', $buyer->id)
            ->where('product_id', $product->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($order, 'Order harus terbentuk.');
        $this->assertEquals($ombags->id, $order->affiliate_id, 'Pemilik kupon harus jadi affiliate.');
        $this->assertEquals($topUpline->id, $order->upline_id, 'Upline pemilik kupon harus dapat bonus upline.');
        $this->assertEquals($coupon->code, $order->coupon_code);
        // Diskon 10% dari 100.000 = 10.000, sisa 90.000.
        $this->assertEquals(90000, (int) $order->amount);

        // Setelah dibayar, komisi harus benar-benar terbentuk.
        app(OrderPaymentService::class)->markAsPaid($order->fresh());

        $direct = Commission::where('order_id', $order->id)->where('type', 'direct')->first();
        $this->assertNotNull($direct, 'Komisi direct OmBags harus dibuat.');
        $this->assertEquals($ombags->id, $direct->user_id);
        // 30% dari 90.000 = 27.000
        $this->assertEquals(27000, (int) $direct->amount);

        $upline = Commission::where('order_id', $order->id)->where('type', 'upline')->first();
        $this->assertNotNull($upline, 'Bonus upline rynz2018 harus dibuat.');
        $this->assertEquals($topUpline->id, $upline->user_id);
        // 10% dari 90.000 = 9.000
        $this->assertEquals(9000, (int) $upline->amount);
    }

    public function test_ref_link_still_takes_priority_over_coupon_owner(): void
    {
        $refAffiliateUpline = $this->makeMember();
        $refAffiliate = $this->makeMember($refAffiliateUpline->id);
        $couponOwner = $this->makeMember();
        $buyer = $this->makeMember();

        $product = $this->makeProduct();

        $coupon = Coupon::create([
            'code' => 'KUPON-'.Str::upper(Str::random(4)),
            'name' => 'Kupon',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
        ]);
        $couponOwner->coupons()->attach($coupon->id);

        $this->actingAs($buyer)
            ->withCookie('ref', $refAffiliate->referral_code)
            ->post('/checkout/'.$product->slug, [
                'coupon_code' => $coupon->code,
            ])
            ->assertRedirect();

        $order = Order::where('user_id', $buyer->id)->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertEquals($refAffiliate->id, $order->affiliate_id, 'Ref link harus tetap diprioritaskan.');
        $this->assertEquals($refAffiliateUpline->id, $order->upline_id);
    }

    public function test_no_coupon_no_ref_keeps_no_attribution(): void
    {
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        $this->actingAs($buyer)
            ->post('/checkout/'.$product->slug, [])
            ->assertRedirect();

        $order = Order::where('user_id', $buyer->id)->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertNull($order->affiliate_id);
        $this->assertNull($order->upline_id);
    }
}
