<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPromoTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function makeVendor(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'can_upload_product' => true,
            'referral_code' => 'VENDOR'.uniqid(),
            'name' => 'Vendor Andi',
        ], $overrides));
    }

    private function makeRegularMember(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'can_upload_product' => false,
            'referral_code' => 'REG'.uniqid(),
            'name' => 'Regular Budi',
        ], $overrides));
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'title' => 'Vendor Product',
            'slug' => 'vendor-product-'.uniqid(),
            'description' => 'desc',
            'price' => 100000,
            'product_type' => 'digital',
            'commission_percent' => 10,
        ], $overrides));
    }

    public function test_regular_member_cannot_access_promo_template_crud(): void
    {
        $this->actingAs($this->makeRegularMember())
            ->get('/dashboard/promo-templates')
            ->assertForbidden();
    }

    public function test_vendor_can_view_their_promo_template_list(): void
    {
        $this->actingAs($this->makeVendor())
            ->get('/dashboard/promo-templates')
            ->assertOk();
    }

    public function test_vendor_can_create_promo_template_for_own_product_and_status_is_pending(): void
    {
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);

        $this->actingAs($vendor)
            ->post('/dashboard/promo-templates', [
                'title' => 'Promo Vendor',
                'product_id' => $product->id,
                'body' => 'Beli sekarang juga di {link_produk}',
                'is_active' => '1',
                'order' => 0,
            ])
            ->assertRedirect();

        $template = PromoTemplate::where('title', 'Promo Vendor')->first();
        $this->assertNotNull($template);
        $this->assertEquals($vendor->id, $template->created_by_user_id);
        $this->assertEquals('pending', $template->approval_status);
        $this->assertEquals('product', $template->category);
    }

    public function test_vendor_cannot_create_template_for_other_vendor_product(): void
    {
        $vendor1 = $this->makeVendor();
        $vendor2 = $this->makeVendor();
        $vendor2Product = $this->makeProduct(['created_by' => $vendor2->id]);

        $this->actingAs($vendor1)
            ->post('/dashboard/promo-templates', [
                'title' => 'Bad Promo',
                'product_id' => $vendor2Product->id,
                'body' => 'Should not work',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('product_id');

        $this->assertNull(PromoTemplate::where('title', 'Bad Promo')->first());
    }

    public function test_vendor_cannot_edit_other_vendor_template(): void
    {
        $vendor1 = $this->makeVendor();
        $vendor2 = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor2->id]);
        $template = PromoTemplate::create([
            'title' => 'V2 Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor2->id,
            'approval_status' => 'pending',
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($vendor1)
            ->get('/dashboard/promo-templates/'.$template->id.'/edit')
            ->assertForbidden();
    }

    public function test_vendor_edit_reverts_approved_template_to_pending(): void
    {
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        $template = PromoTemplate::create([
            'title' => 'Old Title',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $this->makeAdmin()->id,
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($vendor)
            ->put('/dashboard/promo-templates/'.$template->id, [
                'title' => 'New Title',
                'product_id' => $product->id,
                'body' => 'New body',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $template->refresh();
        $this->assertEquals('New Title', $template->title);
        $this->assertEquals('pending', $template->approval_status);
        $this->assertNull($template->reviewed_at);
    }

    public function test_pending_template_does_not_appear_in_member_promo_list(): void
    {
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        PromoTemplate::create([
            'title' => 'Pending Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'pending',
            'body' => 'Pending body',
            'is_active' => true,
            'order' => 0,
        ]);

        $regularMember = $this->makeRegularMember();
        $response = $this->actingAs($regularMember)
            ->get('/dashboard/promo?category=product');

        $response->assertOk();
        $response->assertDontSee('Pending Template');
    }

    public function test_admin_can_approve_pending_template(): void
    {
        $admin = $this->makeAdmin();
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        $template = PromoTemplate::create([
            'title' => 'Pending Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'pending',
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($admin)
            ->post('/admin/promo-templates/'.$template->id.'/approve')
            ->assertRedirect();

        $template->refresh();
        $this->assertEquals('approved', $template->approval_status);
        $this->assertEquals($admin->id, $template->reviewed_by_user_id);
        $this->assertNotNull($template->reviewed_at);
    }

    public function test_admin_can_reject_pending_template_with_reason(): void
    {
        $admin = $this->makeAdmin();
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        $template = PromoTemplate::create([
            'title' => 'Pending Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'pending',
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($admin)
            ->post('/admin/promo-templates/'.$template->id.'/reject', [
                'rejection_reason' => 'Konten tidak sesuai pedoman',
            ])
            ->assertRedirect();

        $template->refresh();
        $this->assertEquals('rejected', $template->approval_status);
        $this->assertEquals('Konten tidak sesuai pedoman', $template->rejection_reason);
        $this->assertEquals($admin->id, $template->reviewed_by_user_id);
    }

    public function test_admin_reject_requires_reason(): void
    {
        $admin = $this->makeAdmin();
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        $template = PromoTemplate::create([
            'title' => 'Pending Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'pending',
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($admin)
            ->post('/admin/promo-templates/'.$template->id.'/reject', [])
            ->assertSessionHasErrors('rejection_reason');

        $template->refresh();
        $this->assertEquals('pending', $template->approval_status);
    }

    public function test_member_cannot_approve_template(): void
    {
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        $template = PromoTemplate::create([
            'title' => 'Pending Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'pending',
            'body' => 'Body',
            'is_active' => true,
            'order' => 0,
        ]);

        $this->actingAs($vendor)
            ->post('/admin/promo-templates/'.$template->id.'/approve')
            ->assertForbidden();
    }

    public function test_approved_template_appears_in_member_promo_list(): void
    {
        $admin = $this->makeAdmin();
        $vendor = $this->makeVendor();
        $product = $this->makeProduct(['created_by' => $vendor->id]);
        PromoTemplate::create([
            'title' => 'Approved Vendor Template',
            'category' => 'product',
            'product_id' => $product->id,
            'created_by_user_id' => $vendor->id,
            'approval_status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $admin->id,
            'body' => 'Approved body',
            'is_active' => true,
            'order' => 0,
        ]);

        $member = $this->makeRegularMember();
        $response = $this->actingAs($member)
            ->get('/dashboard/promo?category=product');

        $response->assertOk();
        $response->assertSee('Approved Vendor Template');
    }
}
