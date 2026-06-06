<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    public function isEnabled(): bool
    {
        return Setting::get('pakasir_enabled') === '1'
            && Setting::get('pakasir_slug', '') !== ''
            && Setting::get('pakasir_api_key', '') !== '';
    }

    public function getPaymentUrl(string $orderId, int $amount, ?string $redirectUrl = null): string
    {
        $slug = Setting::get('pakasir_slug', '');
        $url = "https://app.pakasir.com/pay/{$slug}/{$amount}?order_id={$orderId}";

        if ($redirectUrl) {
            $url .= '&redirect=' . urlencode($redirectUrl);
        }

        return $url;
    }

    public function verifyTransaction(string $orderId, int $amount): ?array
    {
        $slug = Setting::get('pakasir_slug', '');
        $apiKey = Setting::get('pakasir_api_key', '');

        if ($slug === '' || $apiKey === '') {
            Log::error('Pakasir slug/api_key kosong');
            return null;
        }

        try {
            $response = Http::timeout(15)->get('https://app.pakasir.com/api/transactiondetail', [
                'project' => $slug,
                'amount' => $amount,
                'order_id' => $orderId,
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Pakasir verifyTransaction gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Pakasir verifyTransaction exception', ['message' => $e->getMessage()]);
        }

        return null;
    }
}
