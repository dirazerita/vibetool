<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductLandingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin dan member bisa menambahkan custom HTML di landing page editor,
 * dan HTML tersebut dirender di halaman landing page publik.
 */
class LandingCustomHtmlTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'referral_code' => 'A'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeMember(): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'can_upload_product' => true,
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

    public function test_admin_can_save_and_view_custom_html(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $customHtml = '<section style="padding:80px 0;background:#f0f9ff"><h2>Custom Section</h2><p>Hello World</p></section>';

        // Update landing page dengan custom_html.
        $this->actingAs($admin)
            ->put('/admin/products/'.$product->id.'/landing-page', [
                'hero_title' => 'Hero Test',
                'custom_html' => $customHtml,
            ])
            ->assertRedirect();

        $lp = ProductLandingPage::where('product_id', $product->id)->first();
        $this->assertNotNull($lp);
        $this->assertStringContainsString('Custom Section', $lp->custom_html);
        $this->assertStringContainsString('Hello World', $lp->custom_html);

        // Lihat di public landing page.
        $response = $this->get('/p/'.$product->slug)->assertOk();
        $response->assertSee('Custom Section');
        $response->assertSee('Hello World');
    }

    public function test_member_can_save_custom_html_for_own_product(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $this->actingAs($member)
            ->put('/dashboard/products/'.$product->id.'/landing-page', [
                'hero_title' => 'Hero Member',
                'custom_html' => '<div id="my-widget">Widget HTML</div>',
            ])
            ->assertRedirect();

        $lp = ProductLandingPage::where('product_id', $product->id)->first();
        $this->assertNotNull($lp);
        $this->assertStringContainsString('Widget HTML', $lp->custom_html);
    }

    public function test_empty_custom_html_stored_as_null(): void
    {
        $admin = $this->makeAdmin();
        $member = $this->makeMember();
        $product = $this->makeProduct($member);

        $this->actingAs($admin)
            ->put('/admin/products/'.$product->id.'/landing-page', [
                'hero_title' => 'Hero Test',
                'custom_html' => '',
            ])
            ->assertRedirect();

        $lp = ProductLandingPage::where('product_id', $product->id)->first();
        $this->assertNotNull($lp);
        $this->assertNull($lp->custom_html);
    }
}