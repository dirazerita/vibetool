<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\VideoTutorial;
use Illuminate\Http\Request;

class VideoTutorialController extends Controller
{
    public function index(Product $product)
    {
        $tutorials = $product->videoTutorials()->orderBy('sort_order')->get();
        return view('admin.products.video-tutorials', compact('product', 'tutorials'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $maxOrder = $product->videoTutorials()->max('sort_order') ?? 0;

        $product->videoTutorials()->create([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product, VideoTutorial $tutorial)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $tutorial->update([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil diperbarui.');
    }

    public function destroy(Product $product, VideoTutorial $tutorial)
    {
        $tutorial->delete();

        return redirect()->route('admin.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil dihapus.');
    }

    public function toggle(Product $product, VideoTutorial $tutorial)
    {
        $tutorial->update(['is_active' => !$tutorial->is_active]);

        $status = $tutorial->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.products.video-tutorials', $product)
            ->with('success', "Video tutorial berhasil {$status}.");
    }

    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:video_tutorials,id',
        ]);

        foreach ($request->order as $index => $id) {
            VideoTutorial::where('id', $id)->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
