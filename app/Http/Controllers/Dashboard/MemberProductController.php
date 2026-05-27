<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberProductController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->canUploadProduct()) {
            abort(403, 'Anda tidak memiliki izin untuk mengupload produk.');
        }

        $products = Product::where('created_by', $user->id)->latest()->paginate(15);

        return view('dashboard.member-products', compact('products'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->canUploadProduct()) {
            abort(403, 'Anda tidak memiliki izin untuk mengupload produk.');
        }

        return view('dashboard.member-products-create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->canUploadProduct()) {
            abort(403, 'Anda tidak memiliki izin untuk mengupload produk.');
        }

        $isFree = $request->input('product_type') === 'free';

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_type' => 'required|in:digital,software,free',
            'price' => $isFree ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'license_duration' => 'nullable|in:1_month,6_months,1_year,lifetime',
            'file' => $isFree ? 'nullable|file|max:102400' : 'nullable|required_without:file_url|file|max:102400',
            'file_url' => $isFree ? 'nullable|url|max:2048' : 'nullable|required_without:file|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ], [
            'file.required_without' => 'Upload file produk atau isi link eksternal.',
            'file_url.required_without' => 'Isi link eksternal atau upload file produk.',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'commission_percent' => 0,
            'commission_percent_non_owner' => 0,
            'upline_percent' => 0,
            'upline_percent_non_owner' => 0,
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'file_url' => $request->input('file_url') ?: null,
            'file_path' => null,
            'is_active' => false,
            'created_by' => $user->id,
            'approval_status' => 'pending',
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('products', 'local');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('dashboard.member-products')->with('success', 'Produk berhasil disubmit! Menunggu persetujuan admin.');
    }

    public function edit(Product $product)
    {
        $user = auth()->user();

        if (!$user->canUploadProduct() || $product->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }

        return view('dashboard.member-products-edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $user = auth()->user();

        if (!$user->canUploadProduct() || $product->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit produk ini.');
        }

        $isFree = $request->input('product_type') === 'free';

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_type' => 'required|in:digital,software,free',
            'price' => $isFree ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'license_duration' => 'nullable|in:1_month,6_months,1_year,lifetime',
            'file' => 'nullable|file|max:102400',
            'file_url' => 'nullable|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'file_url' => $request->input('file_url') ?: null,
        ];

        if ($product->isRejected()) {
            $data['approval_status'] = 'pending';
            $data['rejection_reason'] = null;
        }

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('products', 'local');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('dashboard.member-products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();

        if (!$user->canUploadProduct() || $product->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus produk ini.');
        }

        $product->delete();

        return redirect()->route('dashboard.member-products')->with('success', 'Produk berhasil dihapus.');
    }
}
