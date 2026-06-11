<?php

namespace Tests\Feature;

use App\Models\MemberCommission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Memastikan halaman /dashboard/products menampilkan tarif "Komisi kamu" dan
 * "Bonus upline" mengikuti komisi khusus (MemberCommission) yang di-set admin
 * untuk member tsb, bukan tarif default produk.
 */
class DashboardProductCommissionDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
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
            'commission_percent_non_owner' => 15,
            'upline_percent' => 10,
            'upline_percent_non_owner' => 5,
            'creator_share_percent' => 0,
            'product_type' => 'digital',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    public function test_custom_commission_overrides_default_display(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct();

        MemberCommission::create([
            'user_id' => $member->id,
            'product_id' => $product->id,
            'commission_percent' => 50, // custom direct
            'upline_percent' => 20,     // custom upline
        ]);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Custom rates: 50% dari 100.000 = 50.000 ; 20% = 20.000
        $response->assertSee('Rp 50.000');
        $response->assertSee('(50%)');
        $response->assertSee('Rp 20.000');

        // Default non-owner rates (15% = 15.000 ; 5% = 5.000) tidak boleh muncul.
        $response->assertDontSee('Rp 15.000');
        $response->assertDontSee('(15%)');
    }

    public function test_falls_back_to_default_non_owner_rate_without_custom(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct();

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Tanpa MemberCommission & belum beli → tarif non-owner: 15% = 15.000, upline 5% = 5.000
        $response->assertSee('Rp 15.000');
        $response->assertSee('(15%)');
        $response->assertSee('Rp 5.000');
    }

    public function test_partial_custom_only_commission_keeps_default_upline(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct();

        MemberCommission::create([
            'user_id' => $member->id,
            'product_id' => $product->id,
            'commission_percent' => 50, // custom direct
            'upline_percent' => null,   // upline pakai default
        ]);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Direct custom 50% = 50.000
        $response->assertSee('Rp 50.000');
        $response->assertSee('(50%)');
        // Upline tetap default non-owner 5% = 5.000
        $response->assertSee('Rp 5.000');
    }
}
