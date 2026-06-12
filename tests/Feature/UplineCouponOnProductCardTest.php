<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Memastikan kartu produk di /dashboard/products menampilkan kupon milik UPLINE
 * (kode kupon + harga setelah diskon) untuk downline, dengan prioritas kupon
 * milik member sendiri lebih dulu. Komisi & harga ditampilkan dari harga
 * setelah diskon — konsisten dengan auto-apply kupon upline saat checkout.
 */
class UplineCouponOnProductCardTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ], $overrides));
    }

    private function makeProduct(float $price = 199000): Product
    {
        return Product::create([
            'title' => 'Produk Kupon',
            'slug' => 'produk-'.Str::lower(Str::random(8)),
            'description' => 'desc',
            'price' => $price,
            'commission_percent' => 50,
            'commission_percent_non_owner' => 50,
            'upline_percent' => 10,
            'upline_percent_non_owner' => 10,
            'creator_share_percent' => 0,
            'product_type' => 'digital',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    private function makeCoupon(int $percent = 20): Coupon
    {
        return Coupon::create([
            'code' => 'KUP'.Str::upper(Str::random(5)),
            'name' => 'Diskon '.$percent,
            'discount_type' => 'percent',
            'discount_value' => $percent,
            'min_purchase' => 0,
            'is_active' => true,
        ]);
    }

    public function test_upline_coupon_shown_on_downline_card(): void
    {
        $upline = $this->makeMember();
        $member = $this->makeMember(['upline_id' => $upline->id]);
        $product = $this->makeProduct(199000);
        $coupon = $this->makeCoupon(20);
        $coupon->products()->attach($product->id);
        $upline->coupons()->attach($coupon->id);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Kode kupon upline tampil di kartu.
        $response->assertSee($coupon->code);
        // Harga setelah diskon 20% dari 199.000 = 159.200.
        $response->assertSee('Rp 159.200');
        // Komisi 50% dari 159.200 = 79.600 (bukan 50% dari 199.000 = 99.500).
        $response->assertSee('Rp 79.600');
        $response->assertDontSee('Rp 99.500');
        // Label menyebut kupon upline.
        $response->assertSee('kupon upline');
    }

    public function test_own_coupon_takes_priority_over_upline_coupon(): void
    {
        $upline = $this->makeMember();
        $member = $this->makeMember(['upline_id' => $upline->id]);
        $product = $this->makeProduct(199000);

        $uplineCoupon = $this->makeCoupon(10);
        $uplineCoupon->products()->attach($product->id);
        $upline->coupons()->attach($uplineCoupon->id);

        $ownCoupon = $this->makeCoupon(20);
        $ownCoupon->products()->attach($product->id);
        $member->coupons()->attach($ownCoupon->id);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Kupon sendiri (20%) yang dipakai, bukan kupon upline (10%).
        $response->assertSee($ownCoupon->code);
        $response->assertDontSee($uplineCoupon->code);
        // Harga setelah diskon 20% = 159.200, bukan 10% = 179.100.
        $response->assertSee('Rp 159.200');
        $response->assertDontSee('Rp 179.100');
    }

    public function test_no_coupon_badge_when_neither_has_coupon(): void
    {
        $upline = $this->makeMember();
        $member = $this->makeMember(['upline_id' => $upline->id]);
        $product = $this->makeProduct(199000);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Tidak ada label kupon upline & harga penuh tetap tampil.
        $response->assertDontSee('kupon upline');
        $response->assertSee('Rp 199.000');
        // Komisi dari harga penuh: 50% dari 199.000 = 99.500.
        $response->assertSee('Rp 99.500');
    }

    public function test_upline_coupon_ignored_when_below_min_purchase(): void
    {
        $upline = $this->makeMember();
        $member = $this->makeMember(['upline_id' => $upline->id]);
        $product = $this->makeProduct(100000);

        $coupon = Coupon::create([
            'code' => 'MIN'.Str::upper(Str::random(5)),
            'name' => 'Min tinggi',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'min_purchase' => 500000, // di atas harga produk
            'is_active' => true,
        ]);
        $coupon->products()->attach($product->id);
        $upline->coupons()->attach($coupon->id);

        $response = $this->actingAs($member)
            ->get('/dashboard/products')
            ->assertOk();

        // Kupon tidak memenuhi min_purchase → tidak ditampilkan, harga penuh.
        $response->assertDontSee($coupon->code);
        $response->assertSee('Rp 100.000');
    }
}
