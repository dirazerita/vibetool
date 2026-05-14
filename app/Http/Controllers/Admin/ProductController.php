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
        $products = Product::with('landingPage')->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'file' => 'nullable|required_without:file_url|file|max:102400',
            'file_url' => 'nullable|required_without:file|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ], [
            'file.required_without' => 'Upload file produk atau isi link eksternal.',
            'file_url.required_without' => 'Isi link eksternal atau upload file produk.',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $request->price,
            'commission_percent' => $request->commission_percent,
            'upline_percent' => $request->upline_percent,
            'file_url' => $request->input('file_url') ?: null,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('products', 'local');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'file' => 'nullable|file|max:102400',
            'file_url' => 'nullable|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $request->price,
            'commission_percent' => $request->commission_percent,
            'upline_percent' => $request->upline_percent,
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
