<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        if ($this->secretKey === '') {
            Log::error('Xendit secret key kosong (XENDIT_SECRET_KEY belum di-set di .env)');
            return [
                'error_code' => 'XENDIT_SECRET_KEY_MISSING',
                'message' => 'Xendit secret key belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(15)
                ->post("{$this->baseUrl}/v2/invoices", [
                    'external_id' => $params['external_id'],
                    'amount' => $params['amount'],
                    'payer_email' => $params['payer_email'],
                    'description' => $params['description'],
                    'success_redirect_url' => $params['success_redirect_url'] ?? url('/'),
                    'failure_redirect_url' => $params['failure_redirect_url'] ?? url('/'),
                    'invoice_duration' => 86400,
                ]);
        } catch (\Throwable $e) {
            Log::error('Xendit createInvoice exception', [
                'message' => $e->getMessage(),
                'external_id' => $params['external_id'] ?? null,
            ]);
            return [
                'error_code' => 'XENDIT_HTTP_EXCEPTION',
                'message' => $e->getMessage(),
            ];
        }

        $data = $response->json() ?? [];

        if (!$response->successful() || !isset($data['invoice_url'])) {
            Log::error('Xendit createInvoice gagal', [
                'status' => $response->status(),
                'external_id' => $params['external_id'] ?? null,
                'amount' => $params['amount'] ?? null,
                'response_body' => $data,
            ]);
        }

        return $data;
    }
}
