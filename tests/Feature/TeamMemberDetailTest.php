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
 * Detail member tim (klik node di halaman Tim/Downline). Hanya boleh diakses
 * untuk member yang merupakan downline (keturunan) dari user yang login.
 */
class TeamMemberDetailTest extends TestCase
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
            'title' => 'Produk Detail '.Str::random(5),
            'slug' => 'produk-detail-'.Str::lower(Str::random(6)),
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

    public function test_can_view_direct_downline_detail_with_purchases_and_commission(): void
    {
        $me = $this->makeMember();
        $downline = $this->makeMember($me->id);
        $product = $this->makeProduct();

        // Downline beli, saya sebagai affiliator → komisi 30% = 30.000.
        $order = Order::create([
            'user_id' => $downline->id,
            'product_id' => $product->id,
            'affiliate_id' => $me->id,
            'amount' => $product->price,
            'status' => 'pending',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ]);
        $this->svc->markAsPaid($order->fresh());

        $response = $this->actingAs($me)
            ->get('/dashboard/team/'.$downline->id)
            ->assertOk();

        $response->assertSee($downline->name);
        $response->assertSee($product->title);
        // Komisi untuk viewer = 30.000.
        $response->assertSee('Rp 30.000');
        $response->assertSee('Tim Langsung');
    }

    public function test_can_view_indirect_downline_detail(): void
    {
        $me = $this->makeMember();
        $level2 = $this->makeMember($me->id);
        $level3 = $this->makeMember($level2->id);

        $response = $this->actingAs($me)
            ->get('/dashboard/team/'.$level3->id)
            ->assertOk();

        $response->assertSee($level3->name);
        $response->assertSee('Downline Tim');
    }

    public function test_cannot_view_non_downline_member(): void
    {
        $me = $this->makeMember();
        $stranger = $this->makeMember();

        $this->actingAs($me)
            ->get('/dashboard/team/'.$stranger->id)
            ->assertForbidden();
    }

    public function test_cannot_view_own_upline(): void
    {
        $upline = $this->makeMember();
        $me = $this->makeMember($upline->id);

        // Upline bukan downline saya → dilarang.
        $this->actingAs($me)
            ->get('/dashboard/team/'.$upline->id)
            ->assertForbidden();
    }

    public function test_cannot_view_self(): void
    {
        $me = $this->makeMember();

        $this->actingAs($me)
            ->get('/dashboard/team/'.$me->id)
            ->assertForbidden();
    }
}
