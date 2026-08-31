<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien FAL.AI (fal.run) untuk AI Agent produk:
 * - teks via fal-ai/any-llm (model dipilih dari Settings)
 * - gambar thumbnail via model FLUX.
 *
 * API key disimpan di tabel settings (key: fal_api_key), diisi admin
 * lewat halaman Settings — bukan di .env.
 */
class FalAiService
{
    public const DEFAULT_LLM_MODEL = 'anthropic/claude-3.5-sonnet';
    public const DEFAULT_IMAGE_MODEL = 'fal-ai/flux/schnell';

    public function enabled(): bool
    {
        return trim((string) Setting::get('fal_api_key', '')) !== '';
    }

    private function apiKey(): string
    {
        $key = trim((string) Setting::get('fal_api_key', ''));
        if ($key === '') {
            throw new RuntimeException('FAL.AI API Key belum diisi. Buka Admin → Settings → AI Agent (FAL.AI).');
        }

        return $key;
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

    /** Kirim prompt ke LLM via fal-ai/any-llm, kembalikan teks jawaban. */
    public function llm(string $systemPrompt, string $prompt): string
    {
        $response = Http::withHeaders(['Authorization' => 'Key ' . $this->apiKey()])
            ->timeout(180)
            ->post('https://fal.run/fal-ai/any-llm', [
                'model' => $this->llmModel(),
                'system_prompt' => $systemPrompt,
                'prompt' => $prompt,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('FAL.AI LLM error (' . $response->status() . '): ' . mb_substr($response->body(), 0, 300));
        }

        $output = (string) $response->json('output');
        if (trim($output) === '') {
            throw new RuntimeException('FAL.AI LLM mengembalikan jawaban kosong.');
        }

        return $output;
    }

    /** Generate gambar, kembalikan URL hasil dari FAL. */
    public function generateImageUrl(string $prompt, string $imageSize = 'square_hd'): string
    {
        $response = Http::withHeaders(['Authorization' => 'Key ' . $this->apiKey()])
            ->timeout(180)
            ->post('https://fal.run/' . ltrim($this->imageModel(), '/'), [
                'prompt' => $prompt,
                'image_size' => $imageSize,
                'num_images' => 1,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('FAL.AI image error (' . $response->status() . '): ' . mb_substr($response->body(), 0, 300));
        }

        $url = (string) data_get($response->json(), 'images.0.url');
        if ($url === '') {
            throw new RuntimeException('FAL.AI tidak mengembalikan gambar.');
        }

        return $url;
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
