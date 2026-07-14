<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageResizer;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    /**
     * Langkah 1: pilih produk yang mau dibuat / diperbaiki landing page-nya.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::with('landingPage')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('admin.page-builder.index', compact('products', 'search'));
    }

    /**
     * Langkah 2: buka builder drag & drop untuk produk terpilih.
     */
    public function edit(Product $product)
    {
        $product->load(['landingPage', 'landingPageTestimonials' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }]);

        $landingPage = $product->landingPage;

        // State builder tersimpan (kalau halaman pernah dibuat via builder).
        $builderJson = $landingPage?->builder_json;

        return view('admin.page-builder.edit', compact('product', 'landingPage', 'builderJson'));
    }

    /**
     * Simpan hasil builder (AJAX).
     *
     * - builder_json : state blok untuk dibuka ulang di builder
     * - full_html    : hasil kompilasi HTML utuh (mobile-friendly) — dipakai
     *                  pipeline render publik yang sudah ada (use_full_html)
     * - publish      : sekaligus publish halaman atau simpan draft saja
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'builder_json' => 'required|string',
            'full_html' => 'required|string',
            'publish' => 'nullable|boolean',
        ]);

        // Pastikan builder_json adalah JSON valid sebelum disimpan.
        $decoded = json_decode($request->input('builder_json'), true);
        if (! is_array($decoded)) {
            return response()->json(['success' => false, 'message' => 'Data builder tidak valid.'], 422);
        }

        $publish = $request->boolean('publish');

        $data = [
            'builder_json' => $request->input('builder_json'),
            'full_html' => $this->sanitizeFullHtml($request->input('full_html')),
            'use_full_html' => true,
        ];

        // hero_title wajib di tabel — isi dari judul blok hero / judul produk
        // hanya saat landing page belum ada.
        $existing = $product->landingPage;
        if (! $existing) {
            $data['hero_title'] = $product->title;
        }

        if ($publish) {
            $data['is_published'] = true;
        }

        $product->landingPage()->updateOrCreate(
            ['product_id' => $product->id],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => $publish ? 'Halaman disimpan & dipublikasikan.' : 'Draft tersimpan.',
            'published' => $publish || (bool) ($existing?->is_published),
            'preview_url' => route('product.show', $product->slug),
        ]);
    }

    /**
     * Upload gambar dari builder (AJAX) — return URL untuk dipakai di blok.
     */
    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = ImageResizer::resizeGallery($request->file('image'), 'landing-pages/builder');

        return response()->json([
            'success' => true,
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Sanitasi full HTML — pola sama dengan LandingPageController:
     * buang <script>, event handler inline, dan iframe non-whitelist.
     */
    private function sanitizeFullHtml(string $html): string
    {
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<script\b[^>]*\/?>/si', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', '', $html);
        $html = preg_replace_callback('/<iframe\b[^>]*>/si', function ($m) {
            if (preg_match('/src=["\'](https?:)?\/\/(?:www\.)?(?:youtube(?:-nocookie)?\.com\/embed\/|player\.vimeo\.com\/video\/|www\.google\.com\/maps\/embed\?)/i', $m[0])) {
                return $m[0];
            }

            return '';
        }, $html);
        $html = str_replace('</iframe>', '', $html);

        return trim($html);
    }
}
