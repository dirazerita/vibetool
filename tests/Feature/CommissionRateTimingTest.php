<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Memastikan tarif komisi (owner vs non-owner) ditentukan berdasarkan
 * status kepemilikan affiliate/upline PADA SAAT order dibuat, bukan saat
 * commission diproses. Ini mencegah affiliate "naik tarif" secara retroaktif
 * setelah mereka membeli produknya sendiri.
 */
class CommissionRateTimingTest extends TestCase
{
    use RefreshDatabase;

    private OrderPaymentService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = app(OrderPaymentService::class);
    }

    private function makeProduct(int $ownerPct = 30, int $nonOwnerPct = 15, int $uplinePct = 10, int $uplineNonOwnerPct = 5): Product
    {
        return Product::create([
            'title' => 'Test '.Str::random(6),
            'slug' => 'test-'.Str::random(8),
            'description' => 'x',
            'price' => 100000,
            'commission_percent' => $ownerPct,
            'commission_percent_non_owner' => $nonOwnerPct,
            'upline_percent' => $uplinePct,
            'upline_percent_non_owner' => $uplineNonOwnerPct,
            'creator_share_percent' => 0,
            'product_type' => 'digital',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    private function makePendingOrder(User $buyer, Product $product, ?User $affiliate = null, ?User $upline = null): Order
    {
        return Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $affiliate?->id,
            'upline_id' => $upline?->id,
            'amount' => $product->price,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
    }

    private function payOrder(User $buyer, Product $product, ?User $affiliate = null, ?User $upline = null): Order
    {
        $order = $this->makePendingOrder($buyer, $product, $affiliate, $upline);
        $this->svc->markAsPaid($order->fresh());

        return $order->fresh();
    }

    public function test_commission_uses_non_owner_rate_before_affiliate_buys_product(): void
    {
        $product = $this->makeProduct();
        $affiliate = User::factory()->create();
        $buyer = User::factory()->create();

        $order = $this->payOrder($buyer, $product, $affiliate);

        $commission = Commission::where('order_id', $order->id)->where('type', 'direct')->first();
        $this->assertNotNull($commission);
        $this->assertEquals(15000, (int) $commission->amount);
    }

    public function test_commission_uses_owner_rate_after_affiliate_buys_product(): void
    {
        $product = $this->makeProduct();
        $affiliate = User::factory()->create();

        $this->payOrder($affiliate, $product);
        sleep(1);

        $buyer = User::factory()->create();
        $order = $this->payOrder($buyer, $product, $affiliate);

        $commission = Commission::where('order_id', $order->id)->where('type', 'direct')->first();
        $this->assertNotNull($commission);
        $this->assertEquals(30000, (int) $commission->amount);
    }

    public function test_earlier_commissions_keep_their_non_owner_rate_after_affiliate_buys(): void
    {
        $product = $this->makeProduct();
        $affiliate = User::factory()->create();

        $earlierOrders = [];
        for ($i = 0; $i < 3; $i++) {
            $buyer = User::factory()->create();
            $earlierOrders[] = $this->payOrder($buyer, $product, $affiliate);
        }

        sleep(1);
        $this->payOrder($affiliate, $product);

        foreach ($earlierOrders as $o) {
            $c = Commission::where('order_id', $o->id)->where('type', 'direct')->first();
            $this->assertEquals(15000, (int) $c->amount, 'Earlier commission row should not be retroactively updated.');
        }
    }

    public function test_pending_order_before_affiliate_buy_keeps_non_owner_rate_when_paid_after(): void
    {
        $product = $this->makeProduct();
        $affiliate = User::factory()->create();
        $buyer = User::factory()->create();

        $pending = $this->makePendingOrder($buyer, $product, $affiliate);
        sleep(1);

        $this->payOrder($affiliate, $product);
        sleep(1);

        $this->svc->markAsPaid($pending->fresh());

        $c = Commission::where('order_id', $pending->id)->where('type', 'direct')->first();
        $this->assertNotNull($c);
        $this->assertEquals(15000, (int) $c->amount, 'Order yang dibuat sebelum affiliate beli harus tetap pakai rate non-owner walaupun dibayar setelah affiliate beli.');
    }

    public function test_upline_rate_follows_same_time_based_logic(): void
    {
        $product = $this->makeProduct();
        $upline = User::factory()->create();
        $affiliate = User::factory()->create();
        $buyerBefore = User::factory()->create();

        $orderBefore = $this->payOrder($buyerBefore, $product, $affiliate, $upline);
        $cUpBefore = Commission::where('order_id', $orderBefore->id)->where('type', 'upline')->first();
        $this->assertEquals(5000, (int) $cUpBefore->amount);

        sleep(1);
        $this->payOrder($upline, $product);
        sleep(1);

        $buyerAfter = User::factory()->create();
        $orderAfter = $this->payOrder($buyerAfter, $product, $affiliate, $upline);
        $cUpAfter = Commission::where('order_id', $orderAfter->id)->where('type', 'upline')->first();
        $this->assertEquals(10000, (int) $cUpAfter->amount);
    }

    public function test_ownership_is_product_specific(): void
    {
        $productA = $this->makeProduct();
        $productB = $this->makeProduct();
        $affiliate = User::factory()->create();

        $this->payOrder($affiliate, $productA);
        sleep(1);

        $buyerForB = User::factory()->create();
        $orderB = $this->payOrder($buyerForB, $productB, $affiliate);
        $cB = Commission::where('order_id', $orderB->id)->where('type', 'direct')->first();
        $this->assertEquals(15000, (int) $cB->amount, 'Affiliate owns A but not B, should still be non-owner for B.');

        $buyerForA = User::factory()->create();
        $orderA = $this->payOrder($buyerForA, $productA, $affiliate);
        $cA = Commission::where('order_id', $orderA->id)->where('type', 'direct')->first();
        $this->assertEquals(30000, (int) $cA->amount, 'Affiliate owns A, should be owner rate.');
    }

    public function test_paid_at_is_set_when_order_marked_paid(): void
    {
        $product = $this->makeProduct();
        $buyer = User::factory()->create();

        $order = $this->payOrder($buyer, $product);

        $this->assertNotNull($order->paid_at);
        $this->assertEquals('paid', $order->status);
    }

    public function test_display_methods_use_current_state_when_no_timestamp_given(): void
    {
        $product = $this->makeProduct();
        $affiliate = User::factory()->create();

        $this->assertEquals(15.0, $product->commissionPercentFor($affiliate));

        $this->payOrder($affiliate, $product);

        $this->assertEquals(30.0, $product->fresh()->commissionPercentFor($affiliate));
    }
}
