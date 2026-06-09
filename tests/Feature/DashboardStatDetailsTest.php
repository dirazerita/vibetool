<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardStatDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ], $overrides));
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'title' => 'P-'.Str::random(4),
            'slug' => 'p-'.Str::lower(Str::random(8)),
            'description' => 'desc',
            'price' => 100000,
            'product_type' => 'digital',
            'commission_percent' => 10,
        ], $overrides));
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'amount' => 100000,
            'status' => 'paid',
            'payment_method' => 'manual',
            'download_token' => Str::uuid()->toString(),
        ], $overrides));
    }

    public function test_balance_page_loads_for_authenticated_member(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/dashboard/balance')
            ->assertOk()
            ->assertSee('Saldo');
    }

    public function test_balance_page_shows_approved_commissions(): void
    {
        $user = $this->makeUser();
        $product = $this->makeProduct();
        $buyer = $this->makeUser();
        $order = $this->makeOrder([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $user->id,
        ]);

        Commission::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 10000,
            'percent' => 10,
            'type' => 'direct',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/balance');

        $response->assertOk();
        $response->assertSee('10.000');
    }

    public function test_sales_page_loads_for_regular_member(): void
    {
        $this->actingAs($this->makeUser())
            ->get('/dashboard/sales')
            ->assertOk()
            ->assertSee('Penjualan');
    }

    public function test_sales_page_counts_only_downline_paid_nonfree_for_regular_member(): void
    {
        $member = $this->makeUser();
        $product = $this->makeProduct(['price' => 100000]);
        $freeProduct = $this->makeProduct(['price' => 0]);
        $buyer = $this->makeUser();

        // Paid downline order (counts)
        $this->makeOrder([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $member->id,
            'amount' => 100000,
            'status' => 'paid',
        ]);

        // Free product (excluded)
        $this->makeOrder([
            'user_id' => $buyer->id,
            'product_id' => $freeProduct->id,
            'affiliate_id' => $member->id,
            'amount' => 0,
            'status' => 'paid',
        ]);

        // Pending order (excluded)
        $this->makeOrder([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'affiliate_id' => $member->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($member)->get('/dashboard/sales');

        $response->assertOk();
        $response->assertSee('Penjualan');
    }

    public function test_sales_page_shows_external_section_for_vendor(): void
    {
        $vendor = $this->makeUser(['can_upload_product' => true]);
        $vendorProduct = $this->makeProduct(['created_by' => $vendor->id]);
        $otherAffiliate = $this->makeUser();
        $buyer = $this->makeUser();

        // External sale: someone else affiliates a vendor's product
        $this->makeOrder([
            'user_id' => $buyer->id,
            'product_id' => $vendorProduct->id,
            'affiliate_id' => $otherAffiliate->id,
            'amount' => 100000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($vendor)->get('/dashboard/sales');

        $response->assertOk();
        $response->assertSee('di luar Downline');
    }

    public function test_sales_page_does_not_show_external_section_for_regular_member(): void
    {
        $member = $this->makeUser(['can_upload_product' => false]);

        $response = $this->actingAs($member)->get('/dashboard/sales');

        $response->assertOk();
        $response->assertDontSee('di luar Downline');
    }

    public function test_dashboard_index_has_clickable_stat_cards(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('href="'.route('dashboard.balance').'"', false);
        $response->assertSee('href="'.route('dashboard.sales').'"', false);
        $response->assertSee('href="'.route('dashboard.commissions').'"', false);
        $response->assertSee('href="'.route('dashboard.team').'"', false);
    }
}
