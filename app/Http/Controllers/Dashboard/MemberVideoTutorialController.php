<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\VideoTutorial;
use Illuminate\Http\Request;

class MemberVideoTutorialController extends Controller
{
    private function authorizeOwnership(Product $product): void
    {
        $user = auth()->user();

        if (! $user->canUploadProduct() || $product->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola video tutorial produk ini.');
        }
    }

    public function index(Product $product)
    {
        $this->authorizeOwnership($product);

        $tutorials = $product->videoTutorials()->orderBy('sort_order')->get();

        return view('dashboard.products.video-tutorials', compact('product', 'tutorials'));
    }

    public function store(Request $request, Product $product)
    {
        $this->authorizeOwnership($product);

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

        return redirect()->route('dashboard.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product, VideoTutorial $tutorial)
    {
        $this->authorizeOwnership($product);

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

        return redirect()->route('dashboard.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil diperbarui.');
    }

    public function destroy(Product $product, VideoTutorial $tutorial)
    {
        $this->authorizeOwnership($product);

        $tutorial->delete();

        return redirect()->route('dashboard.products.video-tutorials', $product)
            ->with('success', 'Video tutorial berhasil dihapus.');
    }

    public function toggle(Product $product, VideoTutorial $tutorial)
    {
        $this->authorizeOwnership($product);

        $tutorial->update(['is_active' => ! $tutorial->is_active]);

        $status = $tutorial->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('dashboard.products.video-tutorials', $product)
            ->with('success', "Video tutorial berhasil {$status}.");
    }

    public function reorder(Request $request, Product $product)
    {
        $this->authorizeOwnership($product);

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