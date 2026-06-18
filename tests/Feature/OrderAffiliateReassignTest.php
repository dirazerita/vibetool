<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin bisa mengganti affiliator sebuah pesanan dari halaman pesanan.
 * Upline affiliator baru ikut otomatis di-set, dan komisi dihitung ulang
 * (komisi lama ditarik kembali dari saldo penerima lama, komisi baru
 * dikreditkan ke affiliator + upline baru).
 */
class OrderAffiliateReassignTest extends TestCase
{
    use RefreshDatabase;

    private OrderPaymentService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(OrderPaymentService::class);
    }

    private function makeMember(?int $uplineId = null): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'upline_id' => $uplineId,
            'balance' => 0,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'referral_code' => 'A'.Str::upper(Str::random(6)),
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

    private function makePaidOrder(User $buyer, Product $product, ?User $affiliate = null, ?User $upline = null): Order
    {
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $affiliate?->id,
            'upline_id' => $upline?->id,
            'amount' => $product->price,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
        $this->svc->markAsPaid($order->fresh());

        return $order->fresh();
    }

    public function test_admin_can_assign_affiliate_and_upline_is_auto_set(): void
    {
        $topUpline = $this->makeMember();
        $affiliate = $this->makeMember($topUpline->id);
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        // Order awal tanpa affiliator.
        $order = $this->makePaidOrder($buyer, $product);
        $this->assertNull($order->affiliate_id);
        $this->assertEquals(0, Commission::where('order_id', $order->id)->count());

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => $affiliate->id,
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals($affiliate->id, $order->affiliate_id);
        $this->assertEquals($topUpline->id, $order->upline_id, 'Upline harus otomatis di-set dari upline affiliator.');

        // Komisi direct 30% = 30.000 untuk affiliator.
        $direct = Commission::where('order_id', $order->id)->where('type', 'direct')->first();
        $this->assertNotNull($direct);
        $this->assertEquals($affiliate->id, $direct->user_id);
        $this->assertEquals(30000, (int) $direct->amount);
        $this->assertEquals(30000, (int) $affiliate->fresh()->balance);

        // Bonus upline 10% = 10.000.
        $uplineCommission = Commission::where('order_id', $order->id)->where('type', 'upline')->first();
        $this->assertNotNull($uplineCommission);
        $this->assertEquals($topUpline->id, $uplineCommission->user_id);
        $this->assertEquals(10000, (int) $uplineCommission->amount);
        $this->assertEquals(10000, (int) $topUpline->fresh()->balance);
    }

    public function test_reassign_reverses_old_commission_and_credits_new(): void
    {
        $oldAffiliate = $this->makeMember();
        $newUpline = $this->makeMember();
        $newAffiliate = $this->makeMember($newUpline->id);
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        // Order awal dengan affiliator lama (tanpa upline).
        $order = $this->makePaidOrder($buyer, $product, $oldAffiliate);
        $this->assertEquals(30000, (int) $oldAffiliate->fresh()->balance);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => $newAffiliate->id,
            ])
            ->assertRedirect();

        // Saldo affiliator lama dikembalikan ke 0, komisinya dihapus.
        $this->assertEquals(0, (int) $oldAffiliate->fresh()->balance);
        $this->assertEquals(0, Commission::where('order_id', $order->id)->where('user_id', $oldAffiliate->id)->count());

        // Affiliator baru + upline-nya dapat komisi.
        $this->assertEquals(30000, (int) $newAffiliate->fresh()->balance);
        $this->assertEquals(10000, (int) $newUpline->fresh()->balance);
    }

    public function test_removing_affiliate_reverses_commission(): void
    {
        $affiliate = $this->makeMember();
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        $order = $this->makePaidOrder($buyer, $product, $affiliate);
        $this->assertEquals(30000, (int) $affiliate->fresh()->balance);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => '',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertNull($order->affiliate_id);
        $this->assertNull($order->upline_id);
        $this->assertEquals(0, (int) $affiliate->fresh()->balance);
        $this->assertEquals(0, Commission::where('order_id', $order->id)->whereIn('type', ['direct', 'upline'])->count());
    }

    public function test_buyer_cannot_be_their_own_affiliate(): void
    {
        $buyer = $this->makeMember();
        $product = $this->makeProduct();
        $order = $this->makePaidOrder($buyer, $product);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => $buyer->id,
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertNull($order->affiliate_id);
    }

    public function test_pending_order_sets_attribution_without_creating_commission(): void
    {
        $upline = $this->makeMember();
        $affiliate = $this->makeMember($upline->id);
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => $affiliate->id,
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals($affiliate->id, $order->affiliate_id);
        $this->assertEquals($upline->id, $order->upline_id);
        // Belum lunas → belum ada komisi.
        $this->assertEquals(0, Commission::where('order_id', $order->id)->count());
        $this->assertEquals(0, (int) $affiliate->fresh()->balance);

        // Saat ditandai lunas, komisi baru terbentuk sesuai atribusi.
        $this->svc->markAsPaid($order->fresh());
        $this->assertEquals(30000, (int) $affiliate->fresh()->balance);
        $this->assertEquals(10000, (int) $upline->fresh()->balance);
    }

    private function makeCoupon(): Coupon
    {
        return Coupon::create([
            'code' => 'KUPON-'.Str::upper(Str::random(5)),
            'name' => 'Kupon Uji',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'min_purchase' => 0,
            'is_active' => true,
        ]);
    }

    public function test_assign_coupon_owner_credits_owner_and_upline(): void
    {
        // rynz (upline) -> OmBags (pemilik kupon) -> buyer (downline)
        $topUpline = $this->makeMember();
        $ombags = $this->makeMember($topUpline->id);
        $buyer = $this->makeMember($ombags->id);
        $product = $this->makeProduct();

        $coupon = $this->makeCoupon();
        $ombags->coupons()->attach($coupon->id);

        // Order lama: pakai kupon, sudah lunas, TAPI affiliator kosong (bug lama).
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'coupon_code' => $coupon->code,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
        $this->svc->markAsPaid($order->fresh());
        $order->refresh();
        $this->assertNull($order->affiliate_id);
        $this->assertEquals(0, Commission::where('order_id', $order->id)->count());

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post('/admin/orders/'.$order->id.'/assign-coupon-owner')
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals($ombags->id, $order->affiliate_id, 'Pemilik kupon harus jadi affiliator.');
        $this->assertEquals($topUpline->id, $order->upline_id, 'Upline pemilik kupon dapat bonus upline.');
        $this->assertEquals(30000, (int) $ombags->fresh()->balance);
        $this->assertEquals(10000, (int) $topUpline->fresh()->balance);
    }

    public function test_assign_coupon_owner_prefers_buyer_upline_when_multiple_owners(): void
    {
        $buyerUpline = $this->makeMember();
        $buyer = $this->makeMember($buyerUpline->id);
        $otherOwner = $this->makeMember();
        $product = $this->makeProduct();

        $coupon = $this->makeCoupon();
        // Kupon dimiliki dua member: upline pembeli + member lain.
        $coupon->members()->attach([$buyerUpline->id, $otherOwner->id]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'coupon_code' => $coupon->code,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post('/admin/orders/'.$order->id.'/assign-coupon-owner')
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals($buyerUpline->id, $order->affiliate_id, 'Upline pembeli diutamakan saat kupon punya banyak pemilik.');
    }

    public function test_assign_coupon_owner_fails_without_coupon(): void
    {
        $buyer = $this->makeMember();
        $product = $this->makeProduct();
        $order = $this->makePaidOrder($buyer, $product);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post('/admin/orders/'.$order->id.'/assign-coupon-owner')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($order->fresh()->affiliate_id);
    }

    public function test_reassign_when_commission_withdrawn_records_debt_and_keeps_unrelated_balance(): void
    {
        $oldAffiliate = $this->makeMember();
        $newAffiliate = $this->makeMember();
        $buyer = $this->makeMember();
        $product = $this->makeProduct();

        $order = $this->makePaidOrder($buyer, $product, $oldAffiliate);
        $this->assertEquals(30000, (int) $oldAffiliate->fresh()->balance);

        // Simulasikan affiliator lama sudah menarik komisi 30k, lalu mendapat
        // 5k earning SAH dari order lain → saldo sekarang 5k.
        $oldAffiliate->update(['balance' => 5000]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->put('/admin/orders/'.$order->id.'/affiliate', [
                'affiliate_id' => $newAffiliate->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('warning');

        // Reversal mengurangi PERSIS 30k: 5000 - 30000 = -25000.
        // Saldo sah 5k yang tidak terkait tidak boleh hilang begitu saja;
        // selisih 25k tercatat sebagai hutang (saldo minus).
        $this->assertEquals(-25000, (int) $oldAffiliate->fresh()->balance);
        // Affiliator baru tetap dapat komisi.
        $this->assertEquals(30000, (int) $newAffiliate->fresh()->balance);
    }

    public function test_assign_coupon_owner_rejected_when_order_already_has_affiliate(): void
    {
        $existingAffiliate = $this->makeMember();
        $buyer = $this->makeMember();
        $product = $this->makeProduct();
        $coupon = $this->makeCoupon();
        $couponOwner = $this->makeMember();
        $couponOwner->coupons()->attach($coupon->id);

        // Order sudah punya affiliator + pakai kupon.
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $existingAffiliate->id,
            'amount' => $product->price,
            'coupon_code' => $coupon->code,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post('/admin/orders/'.$order->id.'/assign-coupon-owner')
            ->assertRedirect()
            ->assertSessionHas('error');

        // Affiliator tidak berubah.
        $this->assertEquals($existingAffiliate->id, $order->fresh()->affiliate_id);
    }

    public function test_member_search_endpoint_filters_and_excludes(): void
    {
        $admin = $this->makeAdmin();
        $alice = User::factory()->create(['role' => 'member', 'name' => 'Alice Wonderland', 'email' => 'alice@example.com', 'referral_code' => 'U'.Str::upper(Str::random(6))]);
        $bob = User::factory()->create(['role' => 'member', 'name' => 'Bob Builder', 'email' => 'bob@example.com', 'referral_code' => 'U'.Str::upper(Str::random(6))]);

        $response = $this->actingAs($admin)
            ->getJson('/admin/orders/members/search?q=alice&exclude='.$bob->id)
            ->assertOk();

        $data = $response->json();
        $ids = collect($data)->pluck('id')->all();
        $this->assertContains($alice->id, $ids);
        $this->assertNotContains($bob->id, $ids);
        // Admin tidak boleh muncul sebagai kandidat affiliator.
        $this->assertNotContains($admin->id, $ids);
    }

    public function test_needs_attribution_filter_shows_only_problem_orders(): void
    {
        $buyer = $this->makeMember();
        $affiliate = $this->makeMember();
        $product = $this->makeProduct();
        $coupon = $this->makeCoupon();

        // Order bermasalah: paid + berkupon + tanpa affiliator.
        $problem = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'coupon_code' => $coupon->code,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);

        // Order normal: sudah punya affiliator.
        $normal = $this->makePaidOrder($this->makeMember(), $product, $affiliate);

        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)
            ->get('/admin/orders?filter=needs_attribution')
            ->assertOk();

        $response->assertSee('#'.$problem->id);
        $response->assertDontSee('#'.$normal->id);
    }
}
