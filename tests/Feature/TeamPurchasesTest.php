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
 * Halaman "Pembelian Tim" di member area: menampilkan downline mana yang sudah
 * membeli, produk yang mereka beli, dan komisi yang dihasilkan untuk upline,
 * plus filter downline yang belum membeli (untuk follow-up).
 */
class TeamPurchasesTest extends TestCase
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

    private function makeProduct(): Product
    {
        return Product::create([
            'title' => 'Produk Tim '.Str::random(5),
            'slug' => 'produk-tim-'.Str::lower(Str::random(6)),
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

    /**
     * Downline membeli produk dengan upline (saya) sebagai affiliator, sehingga
     * komisi langsung masuk ke saya.
     */
    private function buyAsDownline(User $buyer, Product $product, User $affiliate, ?User $upline = null): Order
    {
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $affiliate->id,
            'upline_id' => $upline?->id,
            'amount' => $product->price,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
        $this->svc->markAsPaid($order->fresh());

        return $order->fresh();
    }

    public function test_page_shows_buyer_and_non_buyer_counts_and_commission(): void
    {
        $me = $this->makeMember();
        $buyer = $this->makeMember($me->id);       // downline yang beli
        $nonBuyer = $this->makeMember($me->id);     // downline yang belum beli
        $product = $this->makeProduct();

        // Downline beli, saya sebagai affiliator → komisi 30% = 30.000.
        $this->buyAsDownline($buyer, $product, $me);

        $response = $this->actingAs($me)
            ->get('/dashboard/team-purchases')
            ->assertOk();

        $response->assertSee('Pembelian Tim');
        $response->assertSee($buyer->name);
        $response->assertSee($nonBuyer->name);
        // Komisi dari tim = 30.000.
        $response->assertSee('Rp 30.000');
        // Status follow-up untuk yang belum beli.
        $response->assertSee('follow-up');
    }

    public function test_buyers_filter_only_shows_purchasers(): void
    {
        $me = $this->makeMember();
        $buyer = $this->makeMember($me->id);
        $nonBuyer = $this->makeMember($me->id);
        $product = $this->makeProduct();

        $this->buyAsDownline($buyer, $product, $me);

        $response = $this->actingAs($me)
            ->get('/dashboard/team-purchases?filter=buyers')
            ->assertOk();

        $response->assertSee($buyer->name);
        $response->assertDontSee($nonBuyer->name);
    }

    public function test_non_buyers_filter_only_shows_follow_up_targets(): void
    {
        $me = $this->makeMember();
        $buyer = $this->makeMember($me->id);
        $nonBuyer = $this->makeMember($me->id);
        $product = $this->makeProduct();

        $this->buyAsDownline($buyer, $product, $me);

        $response = $this->actingAs($me)
            ->get('/dashboard/team-purchases?filter=non_buyers')
            ->assertOk();

        $response->assertSee($nonBuyer->name);
        $response->assertDontSee($buyer->name);
    }

    public function test_only_own_downlines_are_shown(): void
    {
        $me = $this->makeMember();
        $other = $this->makeMember();
        $myDownline = $this->makeMember($me->id);
        $strangerDownline = $this->makeMember($other->id);

        $response = $this->actingAs($me)
            ->get('/dashboard/team-purchases')
            ->assertOk();

        $response->assertSee($myDownline->name);
        $response->assertDontSee($strangerDownline->name);
    }

    public function test_commission_only_counts_my_commission_not_others(): void
    {
        // rynz (upline saya) -> me -> buyer
        $rynz = $this->makeMember();
        $me = $this->makeMember($rynz->id);
        $buyer = $this->makeMember($me->id);
        $product = $this->makeProduct();

        // Buyer beli: affiliator = me (komisi 30% = 30.000),
        // upline = rynz (bonus upline 10% = 10.000).
        $this->buyAsDownline($buyer, $product, $me, $rynz);

        $response = $this->actingAs($me)
            ->get('/dashboard/team-purchases')
            ->assertOk();

        // Komisi SAYA dari tim = 30.000 (bukan termasuk bonus rynz 10.000).
        $response->assertSee('Rp 30.000');
    }
}
