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
            'file' => 'required|file|max:102400',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $filePath = $request->file('file')->store('products', 'local');

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $request->price,
            'commission_percent' => $request->commission_percent,
            'upline_percent' => $request->upline_percent,
            'file_path' => $filePath,
        ];

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
