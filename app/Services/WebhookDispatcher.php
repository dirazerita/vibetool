<?php

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WebhookDispatcher
{
    public const EVENT_ISSUED = 'license.issued';

    public const EVENT_REVOKED = 'license.revoked';

    public const EVENT_RENEWED = 'license.renewed';

    public const HEADER_SIGNATURE = 'X-Vibetool-Signature';

    public const HEADER_EVENT = 'X-Vibetool-Event';

    public const HEADER_DELIVERY = 'X-Vibetool-Delivery';

    public const HTTP_TIMEOUT_SECONDS = 10;

    /**
     * Send a webhook event for the given license. Returns the delivery row, or
     * null when the product has no webhook URL configured.
     */
    public function dispatchForLicense(License $license, string $event): ?WebhookDelivery
    {
        $product = $license->product;
        if (! $product || ! $this->isEnabled($product)) {
            return null;
        }

        $payload = $this->buildPayload($license, $event);

        return $this->send($product, $license, $event, $payload);
    }

    /**
     * Retry a previously failed delivery using the saved payload. URL is taken
     * from the latest product.webhook_url to allow admin to fix typos.
     */
    public function retry(WebhookDelivery $delivery): WebhookDelivery
    {
        $product = $delivery->product;
        $license = $delivery->license;

        if (! $product) {
            $delivery->update([
                'result' => 'failed',
                'error_message' => 'Product no longer exists.',
                'attempt' => $delivery->attempt + 1,
            ]);

            return $delivery;
        }

        $payload = $delivery->payload;

        return $this->send($product, $license, $delivery->event, $payload, $delivery->attempt + 1);
    }

    public function isEnabled(Product $product): bool
    {
        return ! empty(trim((string) $product->webhook_url));
    }

    public function sign(string $body, string $secret): string
    {
        return 'sha256='.hash_hmac('sha256', $body, $secret);
    }

    private function buildPayload(License $license, string $event): array
    {
        $license->loadMissing(['product', 'user']);

        return [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'license' => [
                'key' => $license->key,
                'assigned_at' => optional($license->assigned_at)->toIso8601String(),
                'expires_at' => optional($license->expires_at)->toIso8601String(),
                'is_lifetime' => $license->expires_at === null,
            ],
            'product' => $license->product ? [
                'id' => $license->product->id,
                'title' => $license->product->title,
                'slug' => $license->product->slug,
            ] : null,
            'user' => $license->user ? [
                'id' => $license->user->id,
                'name' => $license->user->name,
                'email' => $license->user->email,
            ] : null,
        ];
    }

    private function send(Product $product, ?License $license, string $event, array $payload, int $attempt = 1): WebhookDelivery
    {
        $url = (string) $product->webhook_url;
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $secret = (string) ($product->webhook_secret ?? '');
        $signature = $secret !== '' ? $this->sign($body, $secret) : null;
        $deliveryId = (string) Str::uuid();

        $delivery = WebhookDelivery::create([
            'product_id' => $product->id,
            'license_id' => $license?->id,
            'event' => $event,
            'url' => $url,
            'payload' => $payload,
            'signature' => $signature,
            'attempt' => $attempt,
            'result' => 'failed', // updated below
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Vibetool-Webhook/1.0',
            self::HEADER_EVENT => $event,
            self::HEADER_DELIVERY => $deliveryId,
        ];
        if ($signature !== null) {
            $headers[self::HEADER_SIGNATURE] = $signature;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->withBody($body, 'application/json')
                ->post($url);

            $status = $response->status();
            $delivery->update([
                'status_code' => $status,
                'response_body' => mb_substr((string) $response->body(), 0, 2000),
                'result' => $status >= 200 && $status < 300 ? 'success' : 'failed',
                'delivered_at' => now(),
                'error_message' => $status >= 200 && $status < 300 ? null : "HTTP {$status}",
            ]);
        } catch (Throwable $e) {
            Log::warning('Webhook dispatch failed', [
                'product_id' => $product->id,
                'event' => $event,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            $delivery->update([
                'result' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
                'delivered_at' => now(),
            ]);
        }

        return $delivery->fresh();
    }
}
