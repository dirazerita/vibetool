<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageResizer;
use App\Http\Controllers\Controller;
use App\Models\LandingPageImage;
use App\Models\LandingPageTestimonial;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class LandingPageController extends Controller
{
    public function edit(Product $product)
    {
        $product->load(['landingPage', 'landingPageImages', 'landingPageTestimonials']);

        return view('admin.products.landing-page', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|max:5120',
            'video_url' => 'nullable|url|max:500',
            'about_content' => 'nullable|string',
            'is_published' => 'nullable',
            'hero_title_font' => 'nullable|string|max:50',
            'hero_title_size' => 'nullable|string|max:10',
            'hero_title_color' => 'nullable|string|max:10',
            'hero_subtitle_font' => 'nullable|string|max:50',
            'hero_subtitle_color' => 'nullable|string|max:10',
            'about_font' => 'nullable|string|max:50',
            'about_color' => 'nullable|string|max:10',
            'about_bg_color' => 'nullable|string|max:10',
            'testimonial_title_color' => 'nullable|string|max:10',
            'testimonial_bg_color' => 'nullable|string|max:10',
            'custom_html' => 'nullable|string',
            'full_html' => 'nullable|string',
            'use_full_html' => 'nullable',
            'remove_hero_image' => 'nullable',
        ]);

        $aboutContent = $request->input('about_content');
        if (is_string($aboutContent) && $aboutContent !== '') {
            $aboutContent = Purifier::clean($aboutContent, 'landing_content');
        }

        $data = [
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'video_url' => $request->video_url,
            'about_content' => $aboutContent,
            'is_published' => $request->boolean('is_published'),
            'hero_title_font' => $request->input('hero_title_font', 'Poppins'),
            'hero_title_size' => $request->input('hero_title_size', '48px'),
            'hero_title_color' => $request->input('hero_title_color', '#ffffff'),
            'hero_subtitle_font' => $request->input('hero_subtitle_font', 'Poppins'),
            'hero_subtitle_color' => $request->input('hero_subtitle_color', '#e2e8f0'),
            'about_font' => $request->input('about_font', 'Poppins'),
            'about_color' => $request->input('about_color', '#374151'),
            'about_bg_color' => $request->input('about_bg_color', '#ffffff'),
            'testimonial_title_color' => $request->input('testimonial_title_color', '#111827'),
            'testimonial_bg_color' => $request->input('testimonial_bg_color', '#f9fafb'),
        ];

        // HTML kustom — sanitasi via strip_tags agar kompatibel dengan HTML5.
        $customHtml = $request->input('custom_html');
        if (is_string($customHtml) && $customHtml !== '') {
            $data['custom_html'] = $this->sanitizeHtml($customHtml);
        } else {
            $data['custom_html'] = $customHtml ?: null;
        }

        // Full HTML page — hanya buang <script> untuk keamanan
        $fullHtml = $request->input('full_html');
        if (is_string($fullHtml) && $fullHtml !== '') {
            $data['full_html'] = $this->sanitizeFullHtml($fullHtml);
        } else {
            $data['full_html'] = $fullHtml ?: null;
        }

        $data['use_full_html'] = $request->boolean('use_full_html');

        if ($request->hasFile('hero_image')) {
            // Hapus gambar lama (jika ada) sebelum simpan baru
            $existing = $product->landingPage;
            if ($existing && $existing->hero_image) {
                Storage::disk('public')->delete($existing->hero_image);
            }
            $data['hero_image'] = ImageResizer::resizeHero($request->file('hero_image'));
        } elseif ($request->boolean('remove_hero_image')) {
            $existing = $product->landingPage;
            if ($existing && $existing->hero_image) {
                Storage::disk('public')->delete($existing->hero_image);
            }
            $data['hero_image'] = null;
        }

        $product->landingPage()->updateOrCreate(
            ['product_id' => $product->id],
            $data
        );

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Landing page berhasil diperbarui.');
    }

    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $maxOrder = $product->landingPageImages()->max('sort_order') ?? 0;

        foreach ($request->file('images') as $index => $image) {
            $path = ImageResizer::resizeGallery($image);
            $caption = $request->input("captions.{$index}");

            $product->landingPageImages()->create([
                'image_path' => $path,
                'caption' => $caption,
                'sort_order' => ++$maxOrder,
            ]);
        }

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Gambar berhasil diupload.');
    }

    public function deleteImage(Product $product, LandingPageImage $image)
    {
        $image->delete();

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Gambar berhasil dihapus.');
    }

    public function reorderImages(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:landing_page_images,id',
        ]);

        foreach ($request->order as $index => $id) {
            LandingPageImage::where('id', $id)->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function storeTestimonial(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'rating' => $request->rating,
            'content' => $request->content,
            'sort_order' => ($product->landingPageTestimonials()->max('sort_order') ?? 0) + 1,
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = ImageResizer::resizeAvatar($request->file('avatar'));
        }

        $product->landingPageTestimonials()->create($data);

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Testimonial berhasil ditambahkan.');
    }

    public function updateTestimonial(Request $request, Product $product, LandingPageTestimonial $testimonial)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $data = [
            'name' => $request->name,
            'rating' => $request->rating,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = ImageResizer::resizeAvatar($request->file('avatar'));
        }

        $testimonial->update($data);

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Testimonial berhasil diperbarui.');
    }

    public function deleteTestimonial(Product $product, LandingPageTestimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Testimonial berhasil dihapus.');
    }

    public function toggleTestimonial(Product $product, LandingPageTestimonial $testimonial)
    {
        $testimonial->update(['is_active' => ! $testimonial->is_active]);

        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Status testimonial berhasil diubah.');
    }

    /**
     * Sanitasi HTML kustom — lebih sederhana dan kompatibel dibanding Purifier
     * yang tidak mendukung HTML5 elements tanpa setup kompleks.
     */
    private function sanitizeHtml(string $html): string
    {
        // 1. Buang DOCTYPE dan komentar HTML
        $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // 2. Ekstrak isi <body> — buang wrapper html/head sebelum body
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $html, $m)) {
            $html = $m[1];
        } else {
            $html = preg_replace('/<html[^>]*>|<\/html>/si', '', $html);
            $html = preg_replace('/<head[^>]*>.*?<\/head>/si', '', $html);
        }

        // 3. Hapus tag & atribut berbahaya
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);   // inline script
        $html = preg_replace('/<script\b[^>]*\/?>/si', '', $html);             // self-closing script
        $html = preg_replace('/<meta\b[^>]*\/?>/si', '', $html);
        $html = preg_replace('/<link\b[^>]*\/?>/si', '', $html);
        $html = preg_replace('/<title\b[^>]*>.*?<\/title>/si', '', $html);
        $html = preg_replace('/<head\b[^>]*>.*?<\/head>/si', '', $html);
        $html = preg_replace('/<\/?html[^>]*>/si', '', $html);

        // 4. Hapus event handler inline (onclick, onload, dll) — XSS prevention
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', '', $html);

        // 5. Hapus <iframe> dari sumber tidak aman, biarkan YouTube/Vimeo/Maps
        $html = preg_replace_callback('/<iframe\b[^>]*>/si', function ($m) {
            if (preg_match('/src=["\'](https?:)?\/\/(?:www\.)?(?:youtube(?:-nocookie)?\.com\/embed\/|player\.vimeo\.com\/video\/|www\.google\.com\/maps\/embed\?)/i', $m[0])) {
                return $m[0];
            }

            return '';
        }, $html);
        $html = str_replace('</iframe>', '', $html);

        return trim($html);
    }

    /**
     * Sanitasi full HTML page — lebih longgar karena halaman ini berdiri sendiri.
     * Hanya buang <script>, event handler, dan iframe non-aman.
     */
    private function sanitizeFullHtml(string $html): string
    {
        // Hapus HTML comments
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        // Hapus <script> inline & external
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<script\b[^>]*\/?>/si', '', $html);
        // Hapus event handler (on*)
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', '', $html);
        // Hapus <iframe> bukan YouTube/Vimeo/Maps
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
