<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien FAL.AI (fal.run) untuk AI Agent produk:
 * - teks via fal-ai/any-llm (model dipilih dari Settings)
 * - gambar thumbnail via model FLUX.
 *
 * API key disimpan di tabel settings, diisi admin lewat halaman Settings:
 * - fal_api_keys : JSON array semua key (utama + cadangan)
 * - fal_api_key  : key yang sedang aktif
 * Saat key aktif gagal (401/402/403/429 atau kehabisan saldo), request
 * otomatis dicoba ulang dengan key cadangan; key cadangan yang berhasil
 * dipromosikan menjadi key aktif.
 */
class FalAiService
{
    public const DEFAULT_LLM_MODEL = 'anthropic/claude-3.5-sonnet';
    public const DEFAULT_IMAGE_MODEL = 'fal-ai/flux/schnell';

    public function enabled(): bool
    {
        return $this->keys() !== [];
    }

    /** Semua key tersimpan: key aktif di urutan pertama, lalu cadangan. */
    public function keys(): array
    {
        $active = trim((string) Setting::get('fal_api_key', ''));

        $stored = json_decode((string) Setting::get('fal_api_keys', '[]'), true);
        $all = array_values(array_unique(array_filter(array_map(
            fn ($k) => trim((string) $k),
            is_array($stored) ? $stored : [],
        ))));

        if ($active !== '' && ! in_array($active, $all, true)) {
            array_unshift($all, $active);
        } elseif ($active !== '') {
            $all = array_merge([$active], array_values(array_diff($all, [$active])));
        }

        return $all;
    }

    public function llmModel(): string
    {
        $model = trim((string) Setting::get('fal_llm_model', ''));

        return $model !== '' ? $model : self::DEFAULT_LLM_MODEL;
    }

    public function imageModel(): string
    {
        $model = trim((string) Setting::get('fal_image_model', ''));

        return $model !== '' ? $model : self::DEFAULT_IMAGE_MODEL;
    }

    /**
     * Jalankan request dengan failover antar key.
     * $request menerima satu key dan mengembalikan Response.
     */
    private function withFailover(callable $request, string $label): Response
    {
        $keys = $this->keys();
        if ($keys === []) {
            throw new RuntimeException('FAL.AI API Key belum diisi. Buka Admin → Settings → AI Agent (FAL.AI).');
        }

        $lastMessage = 'Semua API Key gagal.';
        foreach ($keys as $i => $key) {
            /** @var Response $response */
            $response = $request($key);

            if ($response->successful()) {
                if ($i > 0) {
                    // Key cadangan berhasil — jadikan key aktif untuk request berikutnya.
                    Setting::set('fal_api_key', $key);
                }

                return $response;
            }

            $body = strtolower($response->body());
            $keyProblem = in_array($response->status(), [401, 402, 403, 429], true)
                || str_contains($body, 'balance')
                || str_contains($body, 'exhausted')
                || str_contains($body, 'insufficient');

            $lastMessage = "FAL.AI {$label} error (" . $response->status() . ', key #' . ($i + 1) . '): '
                . mb_substr($response->body(), 0, 300);

            if (! $keyProblem) {
                // Error bukan karena key (mis. prompt tidak valid) — percuma ganti key.
                break;
            }
        }

        throw new RuntimeException($lastMessage);
    }

    /** Kirim prompt ke LLM via fal-ai/any-llm, kembalikan teks jawaban. */
    public function llm(string $systemPrompt, string $prompt): string
    {
        $response = $this->withFailover(
            fn (string $key) => Http::withHeaders(['Authorization' => 'Key ' . $key])
                ->timeout(180)
                ->post('https://fal.run/fal-ai/any-llm', [
                    'model' => $this->llmModel(),
                    'system_prompt' => $systemPrompt,
                    'prompt' => $prompt,
                ]),
            'LLM',
        );

        $output = (string) $response->json('output');
        if (trim($output) === '') {
            throw new RuntimeException('FAL.AI LLM mengembalikan jawaban kosong.');
        }

        return $output;
    }

    /** Generate gambar, kembalikan URL hasil dari FAL. */
    public function generateImageUrl(string $prompt, string $imageSize = 'square_hd'): string
    {
        $response = $this->withFailover(
            fn (string $key) => Http::withHeaders(['Authorization' => 'Key ' . $key])
                ->timeout(180)
                ->post('https://fal.run/' . ltrim($this->imageModel(), '/'), [
                    'prompt' => $prompt,
                    'image_size' => $imageSize,
                    'num_images' => 1,
                ]),
            'image',
        );

        $url = (string) data_get($response->json(), 'images.0.url');
        if ($url === '') {
            throw new RuntimeException('FAL.AI tidak mengembalikan gambar.');
        }

        return $url;
    }

    /**
     * Daftar model teks yang didukung fal-ai/any-llm (dari enum di skema
     * OpenAPI endpoint-nya). Cache 1 jam. Return: [['id' =>, 'name' =>], ...]
     */
    public function listTextModels(): array
    {
        return cache()->remember('fal_text_models', 3600, function () {
            $response = $this->platformGet('https://api.fal.ai/v1/models', [
                'endpoint_id' => 'fal-ai/any-llm',
                'expand' => 'openapi-3.0',
            ]);

            $enum = data_get(
                $response->json(),
                'models.0.openapi.components.schemas.AnyLlmInput.properties.model.enum',
            );
            if (! is_array($enum) || $enum === []) {
                throw new RuntimeException('Daftar model teks tidak ditemukan di skema any-llm.');
            }

            return array_map(fn ($id) => ['id' => (string) $id, 'name' => (string) $id], $enum);
        });
    }

    /**
     * Daftar model text-to-image di FAL (maks 2 halaman × 100). Cache 1 jam.
     * Return: [['id' => endpoint_id, 'name' => display name], ...]
     */
    public function listImageModels(): array
    {
        return cache()->remember('fal_image_models', 3600, function () {
            $models = [];
            $cursor = null;

            for ($page = 0; $page < 2; $page++) {
                $query = ['category' => 'text-to-image', 'limit' => 100];
                if ($cursor) {
                    $query['cursor'] = $cursor;
                }

                $json = $this->platformGet('https://api.fal.ai/v1/models', $query)->json();

                foreach ((array) data_get($json, 'models', []) as $m) {
                    if (data_get($m, 'metadata.status') === 'deprecated') {
                        continue;
                    }
                    $models[] = [
                        'id' => (string) data_get($m, 'endpoint_id'),
                        'name' => (string) (data_get($m, 'metadata.display_name') ?: data_get($m, 'endpoint_id')),
                    ];
                }

                $cursor = data_get($json, 'next_cursor');
                if (! data_get($json, 'has_more') || ! $cursor) {
                    break;
                }
            }

            if ($models === []) {
                throw new RuntimeException('Daftar model gambar kosong dari FAL.');
            }

            return $models;
        });
    }

    /** GET ke Platform API FAL; sertakan key aktif bila ada (rate limit lebih tinggi). */
    private function platformGet(string $url, array $query): Response
    {
        $request = Http::timeout(30);
        $key = $this->keys()[0] ?? '';
        if ($key !== '') {
            $request = $request->withHeaders(['Authorization' => 'Key ' . $key]);
        }

        $response = $request->get($url, $query);
        if (! $response->successful()) {
            throw new RuntimeException('FAL Platform API error (' . $response->status() . '): ' . mb_substr($response->body(), 0, 200));
        }

        return $response;
    }

    /**
     * Cek saldo kredit sebuah key (default: key aktif).
     * Return: ['balance' => float, 'currency' => string].
     */
    public function balance(?string $key = null): array
    {
        $key = trim((string) ($key ?? ($this->keys()[0] ?? '')));
        if ($key === '') {
            throw new RuntimeException('FAL.AI API Key belum diisi.');
        }

        $response = Http::withHeaders(['Authorization' => 'Key ' . $key])
            ->timeout(30)
            ->get('https://api.fal.ai/v1/account/billing', ['expand' => 'credits']);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Gagal cek saldo FAL (' . $response->status() . '). '
                . 'Pastikan API Key valid dan punya scope ADMIN. Detail: '
                . mb_substr($response->body(), 0, 200),
            );
        }

        $balance = data_get($response->json(), 'credits.current_balance');
        if ($balance === null) {
            throw new RuntimeException('Respons FAL tidak memuat saldo. Key mungkin bukan tipe ADMIN.');
        }

        return [
            'balance' => (float) $balance,
            'currency' => (string) data_get($response->json(), 'credits.currency', 'USD'),
        ];
    }

    /** Ambil objek JSON pertama dari jawaban LLM (toleran terhadap ```json fence). */
    public static function extractJson(string $text): array
    {
        $clean = preg_replace('/```(?:json)?/i', '', $text);
        if (preg_match('/\{.*\}/s', $clean, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException('Jawaban AI tidak berformat JSON yang valid.');
    }
}
