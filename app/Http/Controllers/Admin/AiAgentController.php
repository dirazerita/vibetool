<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageResizer;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductLandingPage;
use App\Services\FalAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * AI Agent (FAL.AI): dari link repo → judul + deskripsi + thumbnail → produk
 * → landing page otomatis. Dipanggil bertahap via AJAX dari halaman
 * Tambah Produk supaya ada progress dan tidak kena timeout PHP.
 */
class AiAgentController extends Controller
{
    public function __construct(private readonly FalAiService $fal)
    {
    }

    /** Daftar model FAL untuk dropdown di Settings (?type=text|image). */
    public function models(Request $request): JsonResponse
    {
        $request->validate(['type' => ['required', 'in:text,image']]);

        try {
            $models = $request->input('type') === 'text'
                ? $this->fal->listTextModels()
                : $this->fal->listImageModels();

            return response()->json(['ok' => true, 'models' => $models]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Cek saldo kredit akun FAL (dipakai tombol Cek Saldo di Settings). */
    public function balance(Request $request): JsonResponse
    {
        $request->validate(['key' => ['nullable', 'string', 'max:255']]);

        $key = trim((string) $request->input('key', ''));
        if ($key === '' && ! $this->fal->enabled()) {
            return response()->json(['ok' => false, 'message' => 'FAL.AI API Key belum diisi.'], 422);
        }

        try {
            $data = $this->fal->balance($key !== '' ? $key : null);

            return response()->json([
                'ok' => true,
                'balance' => $data['balance'],
                'currency' => $data['currency'],
                'formatted' => '$' . number_format($data['balance'], 2) . ' ' . $data['currency'],
            ]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Step 1: baca repo lalu minta LLM menyusun data produk (JSON). */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate(['repo_url' => ['required', 'url', 'max:500']]);

        if (! $this->fal->enabled()) {
            return response()->json([
                'ok' => false,
                'message' => 'FAL.AI API Key belum diisi. Buka Admin → Settings → bagian AI Agent (FAL.AI).',
            ], 422);
        }

        try {
            $context = $this->fetchRepoContext($request->input('repo_url'));

            $system = <<<'SYS'
Kamu adalah AI Agent untuk marketplace produk digital Indonesia bernama VibeTool.
Tugasmu: dari informasi sebuah repositori/halaman produk, susun data produk siap jual.
Jawab HANYA dengan satu objek JSON valid tanpa teks lain, dengan key persis:
{
 "title": "judul produk menarik, Bahasa Indonesia, maks 60 karakter, tanpa kata 'repo'",
 "description": "deskripsi penjualan 2-3 paragraf Bahasa Indonesia, fokus manfaat untuk pembeli",
 "product_type": "software" (jika berupa aplikasi/tool yang diinstal atau dipakai user) atau "digital" (ebook/template/aset),
 "price_suggestion": harga wajar dalam Rupiah (integer, kelipatan 1000, antara 50000-500000),
 "summary": "ringkasan fungsi produk 3-5 kalimat, untuk bahan landing page",
 "selling_points": ["5-7 poin manfaat singkat Bahasa Indonesia"],
 "thumbnail_prompt": "English prompt for a premium product thumbnail image: modern 3D illustration, dark background with indigo-violet gradient accents, representing the product function. No text, no words, no letters in the image."
}
SYS;

            $answer = $this->fal->llm($system, $context);
            $data = FalAiService::extractJson($answer);

            foreach (['title', 'description', 'summary', 'thumbnail_prompt'] as $key) {
                if (trim((string) ($data[$key] ?? '')) === '') {
                    throw new \RuntimeException("AI tidak mengembalikan field '{$key}'.");
                }
            }

            return response()->json([
                'ok' => true,
                'title' => Str::limit(trim($data['title']), 120, ''),
                'description' => trim($data['description']),
                'product_type' => in_array($data['product_type'] ?? '', ['software', 'digital'], true) ? $data['product_type'] : 'digital',
                'price_suggestion' => max(0, (int) ($data['price_suggestion'] ?? 0)),
                'summary' => trim($data['summary']),
                'selling_points' => array_values(array_filter(array_map('strval', (array) ($data['selling_points'] ?? [])))),
                'thumbnail_prompt' => trim($data['thumbnail_prompt']),
            ]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Step 2: generate thumbnail via FLUX lalu simpan ke storage produk. */
    public function thumbnail(Request $request): JsonResponse
    {
        $request->validate(['prompt' => ['required', 'string', 'max:2000']]);

        try {
            $imageUrl = $this->fal->generateImageUrl($request->input('prompt'));

            $download = Http::timeout(90)->get($imageUrl);
            if (! $download->successful()) {
                throw new \RuntimeException('Gagal mengunduh gambar hasil AI (' . $download->status() . ').');
            }

            $path = ImageResizer::resizeThumbnailFromBytes($download->body());

            return response()->json([
                'ok' => true,
                'thumbnail_path' => $path,
                'thumbnail_url' => asset('storage/' . $path),
            ]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Step 3: buat record produk dari hasil AI. */
    public function createProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'product_type' => ['required', 'in:digital,software'],
            'price' => ['required', 'integer', 'min:0'],
            'thumbnail_path' => ['required', 'string', 'max:255', 'regex:#^products/[\w.\-]+$#'],
            'repo_url' => ['nullable', 'url', 'max:500'],
            'activate' => ['nullable', 'boolean'],
        ]);

        try {
            $product = Product::create([
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'description' => $data['description']
                    . ($data['repo_url'] ? "\n\nSumber: " . $data['repo_url'] : ''),
                'price' => $data['price'],
                'product_type' => $data['product_type'],
                'license_duration' => $data['product_type'] === 'software' ? 'lifetime' : null,
                'max_devices' => $data['product_type'] === 'software' ? 1 : null,
                'commission_percent' => 0,
                'commission_percent_non_owner' => 0,
                'upline_percent' => 0,
                'upline_percent_non_owner' => 0,
                'creator_share_percent' => 0,
                'thumbnail' => $data['thumbnail_path'],
                'is_active' => (bool) ($data['activate'] ?? false),
                'created_by' => $request->user()->id,
                'approval_status' => 'approved',
            ]);

            return response()->json([
                'ok' => true,
                'product_id' => $product->id,
                'edit_url' => route('admin.products.edit', $product),
                'lp_ai_url' => route('admin.products.landing-page-ai', $product),
            ]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Step 4: susun landing page full-HTML via LLM, simpan, dan langsung terapkan. */
    public function generateLandingPage(Request $request, Product $product): JsonResponse
    {
        $request->validate(['summary' => ['nullable', 'string', 'max:5000']]);

        if (! $this->fal->enabled()) {
            return response()->json(['ok' => false, 'message' => 'FAL.AI API Key belum diisi.'], 422);
        }

        try {
            $checkoutUrl = route('checkout', $product->slug);
            $thumbnailUrl = $product->thumbnail ? asset('storage/' . $product->thumbnail) : '';
            $price = number_format((float) $product->price, 0, ',', '.');
            $comparePrice = $product->compare_at_price
                ? number_format((float) $product->compare_at_price, 0, ',', '.')
                : null;

            $system = <<<'SYS'
Kamu adalah desainer + copywriter landing page konversi tinggi untuk produk digital Indonesia.
Buat SATU file HTML lengkap (mulai <!DOCTYPE html> sampai </html>) dengan aturan KERAS:
- DILARANG memakai <script> dalam bentuk apa pun, dilarang atribut on* (onclick dsb), dilarang iframe.
- Semua styling lewat SATU blok <style> di <head>. Tanpa CSS/JS/font eksternal; pakai font stack sistem.
- Mobile-first dan responsif (media query untuk layar kecil).
- Tema: dark premium, aksen gradient indigo (#6366f1) ke violet (#8b5cf6), modern dan profesional.
- Struktur: hero (judul + subjudul + gambar produk + CTA), masalah/solusi, 5-7 kartu manfaat,
  cara kerja 3 langkah, harga (dengan harga coret jika ada) + CTA, FAQ 4-5 item (pakai <details>), CTA penutup, footer singkat.
- Semua copywriting Bahasa Indonesia yang persuasif, spesifik ke produk (bukan lorem ipsum).
- Setiap tombol CTA adalah <a> menuju URL checkout yang diberikan.
- Gambar produk: pakai URL yang diberikan pada tag <img> (jika URL kosong, buat hero tanpa gambar).
Jawab HANYA dengan HTML-nya, tanpa penjelasan, tanpa markdown fence.
SYS;

            $prompt = "Produk: {$product->title}\n"
                . "Deskripsi: {$product->description}\n"
                . 'Ringkasan fungsi: ' . ($request->input('summary') ?: '-') . "\n"
                . "Harga: Rp {$price}" . ($comparePrice ? " (harga coret: Rp {$comparePrice})" : '') . "\n"
                . "URL checkout untuk semua CTA: {$checkoutUrl}\n"
                . "URL gambar produk: {$thumbnailUrl}";

            $html = trim(preg_replace('/^```(?:html)?|```$/mi', '', $this->fal->llm($system, $prompt)));

            if (! Str::contains(Str::lower($html), '</html>')) {
                throw new \RuntimeException('Hasil AI bukan HTML lengkap (terpotong). Coba Generate ulang.');
            }

            $sanitized = $this->sanitizeFullHtml($html);

            $lp = ProductLandingPage::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'ai_html' => $sanitized,
                    'ai_generated_at' => now(),
                    // Langsung terapkan & publikasikan (alur full otomatis).
                    'full_html' => $sanitized,
                    'use_full_html' => true,
                    'is_published' => true,
                ],
            );

            if (! $lp->hero_title) {
                $lp->update(['hero_title' => $product->title]);
            }

            return response()->json([
                'ok' => true,
                'preview_url' => route('product.show', $product->slug),
                'lp_ai_url' => route('admin.products.landing-page-ai', $product),
            ]);
        } catch (Throwable $e) {
            return $this->fail($e);
        }
    }

    /** Tab "Landing Page AI" di halaman edit produk. */
    public function landingPageAi(Product $product)
    {
        $landingPage = $product->landingPage;

        return view('admin.products.landing-page-ai', [
            'product' => $product,
            'landingPage' => $landingPage,
            'falReady' => $this->fal->enabled(),
        ]);
    }

    /** Terapkan ulang ai_html sebagai landing page live (untuk yang sempat diubah manual). */
    public function applyLandingPage(Product $product): JsonResponse
    {
        $lp = $product->landingPage;
        if (! $lp || trim((string) $lp->ai_html) === '') {
            return response()->json(['ok' => false, 'message' => 'Belum ada hasil Landing Page AI untuk produk ini.'], 422);
        }

        $lp->update([
            'full_html' => $lp->ai_html,
            'use_full_html' => true,
            'is_published' => true,
        ]);

        return response()->json(['ok' => true, 'preview_url' => route('product.show', $product->slug)]);
    }

    // =========================================================

    /** Ambil konteks dari GitHub API (meta + README) atau halaman web biasa. */
    private function fetchRepoContext(string $url): string
    {
        if (preg_match('~github\.com/([^/\s]+)/([^/\s#?]+)~i', $url, $m)) {
            $owner = $m[1];
            $repo = preg_replace('/\.git$/', '', $m[2]);
            $headers = ['User-Agent' => 'VibeTool-AI-Agent', 'Accept' => 'application/vnd.github+json'];

            $meta = Http::withHeaders($headers)->timeout(30)
                ->get("https://api.github.com/repos/{$owner}/{$repo}");

            $readme = Http::withHeaders(['User-Agent' => 'VibeTool-AI-Agent', 'Accept' => 'application/vnd.github.raw+json'])
                ->timeout(30)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/readme");

            $parts = ["URL repo: {$url}"];
            if ($meta->successful()) {
                $parts[] = 'Nama: ' . data_get($meta->json(), 'name');
                $parts[] = 'Deskripsi: ' . (data_get($meta->json(), 'description') ?: '-');
                $parts[] = 'Bahasa utama: ' . (data_get($meta->json(), 'language') ?: '-');
                $parts[] = 'Topics: ' . implode(', ', (array) data_get($meta->json(), 'topics', []));
            }
            if ($readme->successful()) {
                $parts[] = "README:\n" . mb_substr($readme->body(), 0, 9000);
            }

            if (count($parts) === 1) {
                throw new \RuntimeException('Repo GitHub tidak bisa diakses (private atau tidak ditemukan).');
            }

            return implode("\n", $parts);
        }

        // Bukan GitHub: ambil teks halaman apa adanya.
        $page = Http::withHeaders(['User-Agent' => 'VibeTool-AI-Agent'])->timeout(30)->get($url);
        if (! $page->successful()) {
            throw new \RuntimeException('Halaman tidak bisa diakses (' . $page->status() . ').');
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags(
            preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $page->body()),
        )));

        return "URL: {$url}\nIsi halaman:\n" . mb_substr($text, 0, 9000);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'produk-ai';
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /** Sama seperti sanitasi Page Builder: buang script, handler inline, iframe. */
    private function sanitizeFullHtml(string $html): string
    {
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<script\b[^>]*\/>/is', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*\'[^\']*\'/i', '', $html);
        $html = preg_replace('/\son\w+\s*=\s*[^\s>]+/i', '', $html);
        $html = preg_replace_callback('/<iframe\b[^>]*>/i', function ($m) {
            $tag = $m[0];
            if (preg_match('/src\s*=\s*["\']?(https?:)?\/\/(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|google\.com\/maps)/i', $tag)) {
                return $tag;
            }

            return '';
        }, $html);

        return trim($html);
    }

    private function fail(Throwable $e): JsonResponse
    {
        report($e);

        return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
    }
}
