<?php

namespace Tests\Feature\Webhook;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Services\WebhookDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LicenseWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_signs_payload_with_hmac_sha256(): void
    {
        $dispatcher = app(WebhookDispatcher::class);
        $body = '{"event":"license.issued"}';

        $sig = $dispatcher->sign($body, 'secret-abc');

        $this->assertSame('sha256='.hash_hmac('sha256', $body, 'secret-abc'), $sig);
    }

    public function test_no_webhook_dispatched_when_product_has_no_url(): void
    {
        Http::fake();
        $license = $this->seedAssignedLicense(['webhook_url' => null]);

        $this->assertSame(0, WebhookDelivery::count());
        // Touch the license again — still no webhook.
        $license->update(['expires_at' => now()->addYear()]);
        $this->assertSame(0, WebhookDelivery::count());

        Http::assertNothingSent();
    }

    public function test_dispatches_license_issued_when_license_created_assigned(): void
    {
        Http::fake([
            'https://hook.example.com/*' => Http::response(['ok' => true], 200),
        ]);

        $license = $this->seedAssignedLicense([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ]);

        $this->assertSame(1, WebhookDelivery::count());

        $delivery = WebhookDelivery::first();
        $this->assertSame('license.issued', $delivery->event);
        $this->assertSame('success', $delivery->result);
        $this->assertSame(200, $delivery->status_code);
        $this->assertSame((int) $license->id, (int) $delivery->license_id);

        Http::assertSent(function (HttpRequest $req) {
            $body = $req->body();
            $sig = $req->header('X-Vibetool-Signature')[0] ?? null;
            $this->assertSame('sha256='.hash_hmac('sha256', $body, 'shh'), $sig);
            $this->assertSame('license.issued', $req->header('X-Vibetool-Event')[0] ?? null);
            $this->assertNotEmpty($req->header('X-Vibetool-Delivery')[0] ?? null);

            return true;
        });
    }

    public function test_does_not_dispatch_for_unassigned_pool_keys(): void
    {
        Http::fake();
        $product = $this->seedProduct([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ]);

        // Create unassigned key (no order_id / user_id) — should NOT trigger.
        License::create(['product_id' => $product->id, 'key' => 'POOL-001']);

        $this->assertSame(0, WebhookDelivery::count());
        Http::assertNothingSent();
    }

    public function test_dispatches_license_revoked_on_delete(): void
    {
        Http::fake([
            'https://hook.example.com/*' => Http::response('', 204),
        ]);

        $license = $this->seedAssignedLicense([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ]);

        WebhookDelivery::truncate(); // ignore the issued event

        $license->delete();

        $this->assertSame(1, WebhookDelivery::count());
        $delivery = WebhookDelivery::first();
        $this->assertSame('license.revoked', $delivery->event);
        $this->assertSame('success', $delivery->result);
    }

    public function test_dispatches_license_renewed_when_expires_extended(): void
    {
        Http::fake([
            'https://hook.example.com/*' => Http::response('', 200),
        ]);

        $license = $this->seedAssignedLicense([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ], expiresAt: now()->addMonth());

        WebhookDelivery::truncate(); // ignore issued

        $license->update(['expires_at' => now()->addYear()]);

        $this->assertSame(1, WebhookDelivery::count());
        $this->assertSame('license.renewed', WebhookDelivery::first()->event);
    }

    public function test_marks_delivery_failed_on_5xx_response(): void
    {
        Http::fake([
            'https://hook.example.com/*' => Http::response('Internal Server Error', 500),
        ]);

        $license = $this->seedAssignedLicense([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ]);

        $delivery = WebhookDelivery::first();
        $this->assertSame('failed', $delivery->result);
        $this->assertSame(500, $delivery->status_code);
        $this->assertSame('HTTP 500', $delivery->error_message);
    }

    public function test_retry_recreates_delivery_with_incremented_attempt(): void
    {
        // Two sequential responses: first fail, retry succeed.
        Http::fake([
            'https://hook.example.com/*' => Http::sequence()
                ->push('boom', 502)
                ->push('ok', 200),
        ]);

        $license = $this->seedAssignedLicense([
            'webhook_url' => 'https://hook.example.com/in',
            'webhook_secret' => 'shh',
        ]);
        $original = WebhookDelivery::first();
        $this->assertSame('failed', $original->result);

        $retried = app(WebhookDispatcher::class)->retry($original);

        $this->assertSame(2, $retried->attempt);
        $this->assertSame('success', $retried->result);
        $this->assertSame(200, $retried->status_code);
        // Original row untouched
        $this->assertSame('failed', $original->fresh()->result);
        $this->assertSame(2, WebhookDelivery::count());
    }

    private function seedProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
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
        ], $attrs));
    }

    private function seedAssignedLicense(array $productAttrs = [], ?\DateTimeInterface $expiresAt = null): License
    {
        $user = User::factory()->create(['status' => 'active']);
        $product = $this->seedProduct($productAttrs);

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
