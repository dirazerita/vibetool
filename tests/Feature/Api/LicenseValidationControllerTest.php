<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\LicenseDevice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseValidationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_license_not_found_when_key_is_invalid(): void
    {
        $response = $this->postJson('/api/license/validate', [
            'key' => 'DOES-NOT-EXIST',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'valid' => false,
            'error' => 'license_not_found',
        ]);
    }

    public function test_validates_a_real_assigned_license_without_device(): void
    {
        $license = $this->seedAssignedLicense(['max_devices' => 1]);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('license.key', $license->key);
        $response->assertJsonPath('license.is_lifetime', true);
        $response->assertJsonMissingPath('device');
    }

    public function test_registers_a_new_device_when_fingerprint_is_provided(): void
    {
        $license = $this->seedAssignedLicense(['max_devices' => 2]);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
            'device_fingerprint' => 'abc123',
            'device_label' => 'Laptop Budi',
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('device.fingerprint', 'abc123');
        $response->assertJsonPath('device.label', 'Laptop Budi');
        $response->assertJsonPath('max_devices', 2);

        $this->assertDatabaseHas('license_devices', [
            'license_id' => $license->id,
            'fingerprint' => 'abc123',
            'label' => 'Laptop Budi',
        ]);
    }

    public function test_touches_existing_device_without_creating_duplicate(): void
    {
        $license = $this->seedAssignedLicense(['max_devices' => 1]);
        LicenseDevice::create([
            'license_id' => $license->id,
            'fingerprint' => 'pc-001',
            'label' => 'Lama',
            'first_seen_at' => now()->subDays(3),
            'last_seen_at' => now()->subDays(3),
        ]);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
            'device_fingerprint' => 'pc-001',
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $this->assertSame(1, LicenseDevice::where('license_id', $license->id)->count());
        $this->assertTrue(
            LicenseDevice::where('license_id', $license->id)->value('last_seen_at') > now()->subMinute()
        );
    }

    public function test_rejects_new_device_when_max_devices_reached(): void
    {
        $license = $this->seedAssignedLicense(['max_devices' => 1]);
        LicenseDevice::create([
            'license_id' => $license->id,
            'fingerprint' => 'pc-existing',
            'label' => 'PC Lama',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
            'device_fingerprint' => 'pc-baru',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('valid', false);
        $response->assertJsonPath('error', 'device_limit_exceeded');
        $response->assertJsonPath('max_devices', 1);
        $response->assertJsonCount(1, 'devices');
        $response->assertJsonPath('devices.0.fingerprint', 'pc-existing');
        $this->assertDatabaseMissing('license_devices', ['fingerprint' => 'pc-baru']);
    }

    public function test_allows_second_device_when_max_devices_is_two(): void
    {
        $license = $this->seedAssignedLicense(['max_devices' => 2]);
        LicenseDevice::create([
            'license_id' => $license->id,
            'fingerprint' => 'pc-1',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
            'device_fingerprint' => 'pc-2',
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $this->assertSame(2, LicenseDevice::where('license_id', $license->id)->count());
    }

    public function test_matched_slug_is_returned_for_single_slug_request(): void
    {
        $license = $this->seedAssignedLicense();

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
        ]);

        $response->assertOk();
        $response->assertJsonPath('license.matched_slug', $license->product->slug);
    }

    public function test_validates_when_one_of_multiple_slugs_matches(): void
    {
        $license = $this->seedAssignedLicense();
        $matched = $license->product->slug;

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => ['some-other-slug', $matched, 'yet-another'],
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('license.matched_slug', $matched);
    }

    public function test_returns_not_found_when_none_of_multiple_slugs_match(): void
    {
        $license = $this->seedAssignedLicense();

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => ['nope-1', 'nope-2'],
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'license_not_found');
    }

    public function test_accepts_json_encoded_array_string_for_product_slug(): void
    {
        $license = $this->seedAssignedLicense();
        $payload = json_encode([$license->product->slug, 'another-slug']);

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $payload,
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('license.matched_slug', $license->product->slug);
    }

    public function test_empty_array_for_product_slug_is_treated_as_no_filter(): void
    {
        $license = $this->seedAssignedLicense();

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => [],
        ]);

        $response->assertOk();
        $response->assertJsonPath('valid', true);
        $response->assertJsonPath('license.matched_slug', $license->product->slug);
    }

    public function test_returns_expired_error_when_license_is_past_expiry(): void
    {
        $license = $this->seedAssignedLicense([
            'max_devices' => 1,
        ], expiresAt: now()->subDay());

        $response = $this->postJson('/api/license/validate', [
            'key' => $license->key,
            'product_slug' => $license->product->slug,
            'device_fingerprint' => 'pc-1',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error', 'license_expired');
        // Devices should NOT be created for expired licenses
        $this->assertDatabaseMissing('license_devices', ['license_id' => $license->id]);
    }

    private function seedAssignedLicense(array $productAttrs = [], ?\DateTimeInterface $expiresAt = null): License
    {
        $user = User::factory()->create(['status' => 'active']);
        $product = Product::create(array_merge([
            'title' => 'Test Software',
            'slug' => 'test-software-'.uniqid(),
            'description' => 't',
            'price' => 100000,
            'commission_percent' => 10,
            'commission_percent_non_owner' => 5,
            'upline_percent' => 5,
            'upline_percent_non_owner' => 2,
            'creator_share_percent' => 0,
            'product_type' => 'software',
            'license_duration' => 'lifetime',
            'max_devices' => 1,
            'is_active' => true,
            'approval_status' => 'approved',
        ], $productAttrs));

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 100000,
            'status' => 'paid',
            'order_code' => 'TST-'.uniqid(),
        ]);

        return License::create([
            'product_id' => $product->id,
            'key' => 'KEY-'.strtoupper(uniqid()),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'assigned_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }
}
