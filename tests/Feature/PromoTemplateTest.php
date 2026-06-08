<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'title' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'description' => 'desc',
            'price' => 100000,
            'product_type' => 'digital',
            'commission_percent' => 10,
        ], $overrides));
    }

    private function makeMember(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'member',
            'status' => 'active',
            'referral_code' => 'TESTREF1',
            'name' => 'Budi Tester',
        ], $overrides));
    }

    public function test_admin_can_view_promo_templates_index(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/admin/promo-templates')
            ->assertOk()
            ->assertSee('Template Promo');
    }

    public function test_member_cannot_access_admin_index(): void
    {
        $this->actingAs($this->makeMember())
            ->get('/admin/promo-templates')
            ->assertForbidden();
    }

    public function test_admin_can_create_member_category_template(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'Ajak teman',
            'category' => 'member',
            'body' => 'Halo, daftar dari link saya: {link_referral}',
            'is_active' => '1',
            'order' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('promo_templates', [
            'title' => 'Ajak teman',
            'category' => 'member',
            'is_active' => true,
            'product_id' => null,
        ]);
    }

    public function test_admin_can_create_product_category_template(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['title' => 'Promo Item']);

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'Promo lebaran',
            'category' => 'product',
            'product_id' => $product->id,
            'body' => '{nama_produk} hanya {harga} — {link_produk}',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('promo_templates', [
            'title' => 'Promo lebaran',
            'category' => 'product',
            'product_id' => $product->id,
        ]);
    }

    public function test_creating_member_category_clears_product_id(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['title' => 'Unused Item']);

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'No product',
            'category' => 'member',
            'product_id' => $product->id,
            'body' => 'Body {nama_member}',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('promo_templates', [
            'title' => 'No product',
            'category' => 'member',
            'product_id' => null,
        ]);
    }

    public function test_validation_rejects_missing_required_fields(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => '',
            'category' => 'invalid',
            'body' => '',
        ])->assertSessionHasErrors(['title', 'category', 'body']);
    }

    public function test_admin_can_update_template(): void
    {
        $admin = $this->makeAdmin();
        $template = PromoTemplate::create([
            'title' => 'Old',
            'category' => 'member',
            'body' => 'Old body',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put("/admin/promo-templates/{$template->id}", [
            'title' => 'New',
            'category' => 'member',
            'body' => 'New body {nama_member}',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('promo_templates', [
            'id' => $template->id,
            'title' => 'New',
            'body' => 'New body {nama_member}',
        ]);
    }

    public function test_admin_can_delete_template(): void
    {
        $admin = $this->makeAdmin();
        $template = PromoTemplate::create([
            'title' => 'X', 'category' => 'member', 'body' => 'x', 'is_active' => true,
        ]);

        $this->actingAs($admin)->delete("/admin/promo-templates/{$template->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('promo_templates', ['id' => $template->id]);
    }

    public function test_member_can_view_promo_index(): void
    {
        $member = $this->makeMember();
        PromoTemplate::create([
            'title' => 'Ajak teman',
            'category' => 'member',
            'body' => 'Daftar di {link_referral} - dari {nama_member}',
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->get('/dashboard/promo')
            ->assertOk()
            ->assertSee('Ajak teman')
            ->assertSee('Budi Tester')
            ->assertSee('?ref=TESTREF1', false);
    }

    public function test_member_promo_renders_product_placeholders(): void
    {
        $member = $this->makeMember();
        $product = $this->makeProduct([
            'title' => 'VibeTool Pro',
            'price' => 199000,
            'compare_at_price' => 299000,
        ]);

        PromoTemplate::create([
            'title' => 'Promo VTPro',
            'category' => 'product',
            'product_id' => $product->id,
            'body' => '{nama_produk} - {harga} (coret {harga_coret}) - {link_produk}',
            'is_active' => true,
        ]);

        $response = $this->actingAs($member)
            ->get('/dashboard/promo?category=product')
            ->assertOk();

        $response->assertSee('VibeTool Pro');
        $response->assertSee('Rp 199.000');
        $response->assertSee('Rp 299.000');
        $response->assertSee('?ref=TESTREF1', false);
    }

    public function test_member_promo_excludes_inactive_templates(): void
    {
        $member = $this->makeMember();
        PromoTemplate::create([
            'title' => 'Hidden Template',
            'category' => 'member',
            'body' => 'hidden',
            'is_active' => false,
        ]);

        $this->actingAs($member)
            ->get('/dashboard/promo')
            ->assertOk()
            ->assertDontSee('Hidden Template');
    }

    public function test_render_substitutes_placeholders_correctly(): void
    {
        $member = User::factory()->make([
            'name' => 'Alice',
            'referral_code' => 'ALC',
        ]);

        $template = new PromoTemplate([
            'category' => 'member',
            'body' => 'Hi from {nama_member}, code: {kode_referral}, url: {link_referral}',
        ]);

        $out = $template->renderFor($member);

        $this->assertStringContainsString('Hi from Alice', $out);
        $this->assertStringContainsString('code: ALC', $out);
        $this->assertStringContainsString('?ref=ALC', $out);
    }
}
