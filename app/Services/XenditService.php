<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class XenditService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key', '');
    }

    public function createInvoice(array $params): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/v2/invoices", [
                'external_id' => $params['external_id'],
                'amount' => $params['amount'],
                'payer_email' => $params['payer_email'],
                'description' => $params['description'],
                'success_redirect_url' => $params['success_redirect_url'] ?? url('/'),
                'failure_redirect_url' => $params['failure_redirect_url'] ?? url('/'),
                'invoice_duration' => 86400,
            ]);

        return $response->json();
    }
}
