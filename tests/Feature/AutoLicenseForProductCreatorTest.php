<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Saat admin menyetujui produk software yang diupload member, pembuat produk
 * otomatis mendapat lisensi untuk produknya sendiri agar bisa test.
 */
class AutoLicenseForProductCreatorTest extends TestCase
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

    private function makeMember(bool $canUpload = true): User
    {
        return User::factory()->create([
            'role' => 'member',
            'status' => 'active',
            'can_upload_product' => $canUpload,
            'referral_code' => 'U'.Str::upper(Str::random(6)),
        ]);
    }

    private function makeProduct(User $owner, string $type = 'software'): Product
    {
        return Product::create([
            'title' => 'Produk '.Str::random(5),
            'slug' => 'produk-'.Str::lower(Str::random(6)),
            'description' => 'desc',
            'price' => 100000,
            'commission_percent' => 30,
            'commission_percent_non_owner' => 30,
            'upline_percent' => 10,
            'upline_percent_non_owner' => 10,
            'creator_share_percent' => 0,
            'product_type' => $type,
            'license_duration' => 'lifetime',
            'is_active' => false,
            'approval_status' => 'pending',
            'created_by' => $owner->id,
        ]);
    }

    public function test_approve_software_product_creates_license_for_creator(): void
    {
        $admin = $this->makeAdmin();
        $creator = $this->makeMember();
        $product = $this->makeProduct($creator, 'software');

        $this->actingAs($admin)
            ->post('/admin/products/'.$product->id.'/approve')
            ->assertRedirect();

        $product->refresh();
        $this->assertEquals('approved', $product->approval_status);

        $license = License::where('product_id', $product->id)
            ->where('user_id', $creator->id)
            ->first();

        $this->assertNotNull($license, 'Lisensi harus dibuat untuk pembuat produk.');
        $this->assertNull($license->order_id, 'Lisensi dibuat tanpa order (auto-assign).');
        $this->assertNotNull($license->key);
        $this->assertNull($license->expires_at, 'Lisensi lifetime, expires_at null.');
    }

    public function test_approve_digital_product_does_not_create_license(): void
    {
        $admin = $this->makeAdmin();
        $creator = $this->makeMember();
        $product = $this->makeProduct($creator, 'digital');

        $this->actingAs($admin)
            ->post('/admin/products/'.$product->id.'/approve')
            ->assertRedirect();

        $license = License::where('product_id', $product->id)
            ->where('user_id', $creator->id)
            ->first();

        $this->assertNull($license);
    }

    public function test_double_approve_does_not_create_duplicate_license(): void
    {
        $admin = $this->makeAdmin();
        $creator = $this->makeMember();
        $product = $this->makeProduct($creator, 'software');

        // Approve pertama.
        $this->actingAs($admin)
            ->post('/admin/products/'.$product->id.'/approve')
            ->assertRedirect();

        $count = License::where('product_id', $product->id)
            ->where('user_id', $creator->id)
            ->count();

        $this->assertEquals(1, $count);

        // Approve kedua (idempoten — tidak buat duplikat).
        $this->actingAs($admin)
            ->post('/admin/products/'.$product->id.'/approve')
            ->assertRedirect();

        $countAfter = License::where('product_id', $product->id)
            ->where('user_id', $creator->id)
            ->count();

        $this->assertEquals(1, $countAfter, 'Tidak boleh ada lisensi duplikat.');
    }

    public function test_creator_sees_license_in_licenses_page(): void
    {
        $admin = $this->makeAdmin();
        $creator = $this->makeMember();
        $product = $this->makeProduct($creator, 'software');

        $this->actingAs($admin)
            ->post('/admin/products/'.$product->id.'/approve');

        $license = License::where('product_id', $product->id)->first();

        $response = $this->actingAs($creator)
            ->get('/dashboard/licenses')
            ->assertOk();

        $response->assertSee($product->title);
        $response->assertSee($license->key);
    }
}