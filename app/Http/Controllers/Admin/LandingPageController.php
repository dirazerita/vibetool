<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageResizer;
use App\Http\Controllers\Controller;
use App\Models\LandingPageImage;
use App\Models\LandingPageTestimonial;
use App\Models\Product;
use App\Models\ProductLandingPage;
use Illuminate\Http\Request;

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
        ]);

        $data = [
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'video_url' => $request->video_url,
            'about_content' => $request->about_content,
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

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = ImageResizer::resizeHero($request->file('hero_image'));
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
        $testimonial->update(['is_active' => !$testimonial->is_active]);
        return redirect()->route('admin.products.landing-page', $product)
            ->with('success', 'Status testimonial berhasil diubah.');
    }
}
