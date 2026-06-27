<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Member yang bisa upload produk harus bisa mengelola Landing Page dan
 * Video Tutorial untuk produknya sendiri. Tombol Landing, Video, Preview
 * muncul di halaman Produk Saya. Akses produk member lain ditolak 403.
 */
class MemberLandingAndVideoTutorialTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(bool $canUpload = true): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'can_upload_product' => $canUpload,
            'balance' => 0,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeProduct(User $owner): Product
    {
        return Product::create([
            'title' => 'Produk Test '.Str::random(5),
            'slug' => 'produk-test-'.Str::lower(Str::random(6)),
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
            'created_by' => $owner->id,
        ]);
    }

    public function test_member_can_access_own_product_landing_page(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $this->actingAs($member)
            ->get('/dashboard/products/'.$product->id.'/landing-page')
            ->assertOk()
            ->assertSee('Landing Page');
    }

    public function test_member_cannot_access_other_members_product_landing_page(): void
    {
        $member = $this->makeMember();
        $other = $this->makeMember();
        $product = $this->makeProduct($other);

        $this->actingAs($member)
            ->get('/dashboard/products/'.$product->id.'/landing-page')
            ->assertForbidden();
    }

    public function test_member_can_access_own_product_video_tutorials(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $this->actingAs($member)
            ->get('/dashboard/products/'.$product->id.'/video-tutorials')
            ->assertOk();
    }

    public function test_member_cannot_access_other_members_product_video_tutorials(): void
    {
        $member = $this->makeMember();
        $other = $this->makeMember();
        $product = $this->makeProduct($other);

        $this->actingAs($member)
            ->get('/dashboard/products/'.$product->id.'/video-tutorials')
            ->assertForbidden();
    }

    public function test_member_without_upload_permission_gets_403(): void
    {
        $noUpload = $this->makeMember(false);
        // Gunakan produk sendiri tapi tanpa izin upload.
        $product = $this->makeProduct($noUpload);

        $this->actingAs($noUpload)
            ->get('/dashboard/products/'.$product->id.'/landing-page')
            ->assertForbidden();
    }

    public function test_produk_saya_shows_landing_video_preview_buttons_for_approved(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $response = $this->actingAs($member)
            ->get('/dashboard/member-products')
            ->assertOk();

        $response->assertSee('Landing Page');
        $response->assertSee('Video Tutorial');
        $response->assertSee('Preview');
    }

    public function test_preview_button_links_to_product_show(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $response = $this->actingAs($member)
            ->get('/dashboard/member-products')
            ->assertOk();

        $response->assertSee(route('product.show', $product->slug));
    }
}