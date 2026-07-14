<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\ImageResizer;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Page Builder untuk member yang punya izin upload produk — cermin dari
 * Admin\PageBuilderController, dibatasi hanya untuk produk milik member
 * sendiri (created_by). Pola ownership sama dengan Dashboard\LandingPageController.
 */
class PageBuilderController extends Controller
{
    private function authorizeOwnership(Product $product): void
    {
        $user = auth()->user();

        if (! $user->canUploadProduct() || $product->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola halaman ini.');
        }
    }

    /**
     * Langkah 1: pilih produk (hanya produk milik member sendiri).
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->canUploadProduct(), 403);

        $search = trim((string) $request->query('q', ''));

        $products = Product::with('landingPage')
            ->where('created_by', $request->user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('dashboard.page-builder.index', compact('products', 'search'));
    }

    /**
     * Langkah 2: buka builder drag & drop untuk produk terpilih.
     */
    public function edit(Product $product)
    {
        $this->authorizeOwnership($product);

        $product->load(['landingPage', 'landingPageTestimonials' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }]);

        $landingPage = $product->landingPage;
        $builderJson = $landingPage?->builder_json;

        return view('dashboard.page-builder.edit', compact('product', 'landingPage', 'builderJson'));
    }

    /**
     * Simpan hasil builder (AJAX) — logika sama dengan versi admin.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeOwnership($product);

        $request->validate([
            'builder_json' => 'required|string',
            'full_html' => 'required|string',
            'publish' => 'nullable|boolean',
        ]);

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
     * Upload gambar dari builder (AJAX).
     */
    public function uploadImage(Request $request, Product $product)
    {
        $this->authorizeOwnership($product);

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
     * Sanitasi full HTML — pola sama dengan Admin\PageBuilderController.
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
