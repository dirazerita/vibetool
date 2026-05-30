<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['landingPage', 'creator'])->latest()->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function pendingProducts()
    {
        $products = Product::with('creator')
            ->where('approval_status', 'pending')
            ->whereNotNull('created_by')
            ->latest()
            ->paginate(15);

        return view('admin.products.pending', compact('products'));
    }

    public function approve(Request $request, Product $product)
    {
        $product->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', 'Produk "'.$product->title.'" berhasil disetujui.');
    }

    public function reject(Request $request, Product $product)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $product->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->back()->with('success', 'Produk "'.$product->title.'" ditolak.');
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $isFree = $request->input('product_type') === 'free';
        if ($isFree) {
            $request->merge([
                'price' => 0,
                'commission_percent' => 0,
                'commission_percent_non_owner' => 0,
                'upline_percent' => 0,
                'upline_percent_non_owner' => 0,
                'creator_share_percent' => 0,
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'commission_percent_non_owner' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'upline_percent_non_owner' => 'required|numeric|min:0|max:100',
            'creator_share_percent' => 'nullable|numeric|min:0|max:100',
            'product_type' => 'required|in:digital,software,free',
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
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'commission_percent' => $isFree ? 0 : $request->commission_percent,
            'commission_percent_non_owner' => $isFree ? 0 : $request->commission_percent_non_owner,
            'upline_percent' => $isFree ? 0 : $request->upline_percent,
            'upline_percent_non_owner' => $isFree ? 0 : $request->upline_percent_non_owner,
            'creator_share_percent' => $isFree ? 0 : ($request->input('creator_share_percent') ?? 0),
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'file_url' => $request->input('file_url') ?: null,
            'file_path' => null,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('products', 'local');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $data['approval_status'] = 'approved';

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $isFree = $request->input('product_type') === 'free';
        if ($isFree) {
            $request->merge([
                'price' => 0,
                'commission_percent' => 0,
                'commission_percent_non_owner' => 0,
                'upline_percent' => 0,
                'upline_percent_non_owner' => 0,
                'creator_share_percent' => 0,
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'commission_percent_non_owner' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'upline_percent_non_owner' => 'required|numeric|min:0|max:100',
            'creator_share_percent' => 'nullable|numeric|min:0|max:100',
            'product_type' => 'required|in:digital,software,free',
            'license_duration' => 'nullable|in:1_month,6_months,1_year,lifetime',
            'file' => 'nullable|file|max:102400',
            'file_url' => 'nullable|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'commission_percent' => $isFree ? 0 : $request->commission_percent,
            'commission_percent_non_owner' => $isFree ? 0 : $request->commission_percent_non_owner,
            'upline_percent' => $isFree ? 0 : $request->upline_percent,
            'upline_percent_non_owner' => $isFree ? 0 : $request->upline_percent_non_owner,
            'creator_share_percent' => $isFree ? 0 : ($request->input('creator_share_percent') ?? 0),
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'is_active' => $request->boolean('is_active'),
            'file_url' => $request->input('file_url') ?: null,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('products', 'local');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
