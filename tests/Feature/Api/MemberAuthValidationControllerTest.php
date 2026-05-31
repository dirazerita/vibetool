<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberAuthValidationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_validates_with_single_slug_string_backward_compat(): void
    {
        [$user, $product] = $this->seedUserWithFreeAccess();

        $response = $this->postJson('/api/auth/validate-member', [
            'email' => $user->email,
            'password' => 'secret123',
            'product_slug' => $product->slug,
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('product.slug', $product->slug);
        $response->assertJsonPath('matched_slug', $product->slug);
    }

    public function test_validates_with_multiple_slugs_one_matches_user_access(): void
    {
        [$user, $product] = $this->seedUserWithFreeAccess();

        // User only has access to $product->slug. Send a list that includes it + extras.
        $response = $this->postJson('/api/auth/validate-member', [
            'email' => $user->email,
            'password' => 'secret123',
            'product_slug' => ['other-slug', $product->slug, 'yet-another'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('matched_slug', $product->slug);
    }

    public function test_returns_no_access_when_user_owns_none_of_the_slugs(): void
    {
        [$user] = $this->seedUserWithFreeAccess();

        // Make a separate active product user has NOT purchased.
        $other = $this->makeProduct(['slug' => 'no-access-product-'.uniqid()]);

        $response = $this->postJson('/api/auth/validate-member', [
            'email' => $user->email,
            'password' => 'secret123',
            'product_slug' => [$other->slug],
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'no_access');
    }

    public function test_returns_product_not_found_when_no_slug_exists(): void
    {
        [$user] = $this->seedUserWithFreeAccess();

        $response = $this->postJson('/api/auth/validate-member', [
            'email' => $user->email,
            'password' => 'secret123',
            'product_slug' => ['ghost-slug-x', 'ghost-slug-y'],
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'product_not_found');
    }

    /**
     * @return array{0:User,1:Product}
     */
    private function seedUserWithFreeAccess(): array
    {
        $user = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $product = $this->makeProduct();

        Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 0,
            'status' => 'paid',
            'order_code' => 'FREE-'.uniqid(),
        ]);

        return [$user, $product];
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'title' => 'Free Tool',
            'slug' => 'free-tool-'.uniqid(),
            'description' => 't',
            'price' => 0,
            'commission_percent' => 0,
            'commission_percent_non_owner' => 0,
            'upline_percent' => 0,
            'upline_percent_non_owner' => 0,
            'creator_share_percent' => 0,
            'product_type' => 'software',
            'license_duration' => 'lifetime',
            'is_active' => true,
            'approval_status' => 'approved',
        ], $attrs));
    }
}
