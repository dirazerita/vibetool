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
}
