<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoTemplate;
use App\Models\PromoTemplateMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_upload_image_with_template(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $image = UploadedFile::fake()->image('promo.jpg', 800, 800)->size(300);

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'Promo dengan gambar',
            'category' => 'member',
            'body' => 'Body {nama_member}',
            'is_active' => '1',
            'images' => [$image],
        ])->assertRedirect();

        $template = PromoTemplate::firstWhere('title', 'Promo dengan gambar');
        $this->assertNotNull($template);
        $this->assertCount(1, $template->media);
        $this->assertSame('image', $template->media->first()->type);
        Storage::disk('public')->assertExists($template->media->first()->path);
    }

    public function test_admin_can_upload_video_with_template(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $video = UploadedFile::fake()->create('promo.mp4', 1024, 'video/mp4');

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'Promo dengan video',
            'category' => 'member',
            'body' => 'Body {nama_member}',
            'is_active' => '1',
            'videos' => [$video],
        ])->assertRedirect();

        $template = PromoTemplate::firstWhere('title', 'Promo dengan video');
        $this->assertNotNull($template);
        $this->assertCount(1, $template->media);
        $this->assertSame('video', $template->media->first()->type);
    }

    public function test_admin_can_upload_multiple_files_on_update(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $template = PromoTemplate::create([
            'title' => 'Multi', 'category' => 'member', 'body' => 'x', 'is_active' => true,
        ]);

        $this->actingAs($admin)->put("/admin/promo-templates/{$template->id}", [
            'title' => 'Multi', 'category' => 'member', 'body' => 'x', 'is_active' => '1',
            'images' => [
                UploadedFile::fake()->image('a.jpg')->size(100),
                UploadedFile::fake()->image('b.png')->size(100),
            ],
            'videos' => [
                UploadedFile::fake()->create('c.mp4', 500, 'video/mp4'),
            ],
        ])->assertRedirect();

        $this->assertCount(3, $template->fresh()->media);
    }

    public function test_upload_rejects_invalid_image_mime(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $bad = UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream');

        $this->actingAs($admin)->post('/admin/promo-templates', [
            'title' => 'Bad', 'category' => 'member', 'body' => 'b', 'is_active' => '1',
            'images' => [$bad],
        ])->assertSessionHasErrors('images.0');
    }

    public function test_admin_can_delete_individual_media_file(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $template = PromoTemplate::create([
            'title' => 'X', 'category' => 'member', 'body' => 'x', 'is_active' => true,
        ]);
        $this->actingAs($admin)->put("/admin/promo-templates/{$template->id}", [
            'title' => 'X', 'category' => 'member', 'body' => 'x', 'is_active' => '1',
            'images' => [UploadedFile::fake()->image('one.jpg')->size(100)],
        ]);
        $media = $template->fresh()->media->first();
        $path = $media->path;

        $this->actingAs($admin)
            ->delete("/admin/promo-templates/{$template->id}/media/{$media->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('promo_template_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_template_also_deletes_media_files(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $template = PromoTemplate::create([
            'title' => 'Y', 'category' => 'member', 'body' => 'y', 'is_active' => true,
        ]);
        $this->actingAs($admin)->put("/admin/promo-templates/{$template->id}", [
            'title' => 'Y', 'category' => 'member', 'body' => 'y', 'is_active' => '1',
            'images' => [UploadedFile::fake()->image('z.jpg')->size(100)],
        ]);
        $path = $template->fresh()->media->first()->path;

        $template->fresh()->delete();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('promo_template_media', ['promo_template_id' => $template->id]);
    }

    public function test_member_view_shows_media_url_and_download_button(): void
    {
        Storage::fake('public');
        $member = $this->makeMember();
        $template = PromoTemplate::create([
            'title' => 'Promo with image', 'category' => 'member', 'body' => 'b {nama_member}', 'is_active' => true,
        ]);
        $template->media()->create([
            'type' => PromoTemplateMedia::TYPE_IMAGE,
            'path' => 'promo-template-media/'.$template->id.'/test.jpg',
            'original_name' => 'test.jpg',
            'mime' => 'image/jpeg',
            'size_bytes' => 1000,
            'sort_order' => 1,
        ]);

        $this->actingAs($member)
            ->get('/dashboard/promo')
            ->assertOk()
            ->assertSee('Download Media')
            ->assertSee('test.jpg');
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
