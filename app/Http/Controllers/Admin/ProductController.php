<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPackage;
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
                'compare_at_price' => null,
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
            'compare_at_price' => 'nullable|numeric|min:0|gte:price',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'commission_percent_non_owner' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'upline_percent_non_owner' => 'required|numeric|min:0|max:100',
            'creator_share_percent' => 'nullable|numeric|min:0|max:100',
            'product_type' => 'required|in:digital,software,free',
            'license_duration' => 'nullable|in:1_month,6_months,1_year,lifetime',
            'max_devices' => 'nullable|integer|min:1|max:100',
            'webhook_url' => 'nullable|url|max:500',
            'webhook_secret' => 'nullable|string|max:128',
            'packages' => 'nullable|array',
            'packages.*.label' => 'nullable|string|max:100',
            'packages.*.duration_type' => 'required_with:packages|in:1_month,6_months,1_year,lifetime',
            'packages.*.price' => 'required_with:packages|numeric|min:0',
            'packages.*.compare_at_price' => 'nullable|numeric|min:0',
            'packages.*.is_active' => 'nullable|boolean',
            'file' => $isFree ? 'nullable|file|max:102400' : 'nullable|required_without:file_url|file|max:102400',
            'file_url' => $isFree ? 'nullable|url|max:2048' : 'nullable|required_without:file|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ], [
            'file.required_without' => 'Upload file produk atau isi link eksternal.',
            'file_url.required_without' => 'Isi link eksternal atau upload file produk.',
            'compare_at_price.gte' => 'Harga coret harus lebih besar atau sama dengan harga jual.',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'compare_at_price' => $isFree ? null : ($request->input('compare_at_price') ?: null),
            'commission_percent' => $isFree ? 0 : $request->commission_percent,
            'commission_percent_non_owner' => $isFree ? 0 : $request->commission_percent_non_owner,
            'upline_percent' => $isFree ? 0 : $request->upline_percent,
            'upline_percent_non_owner' => $isFree ? 0 : $request->upline_percent_non_owner,
            'creator_share_percent' => $isFree ? 0 : ($request->input('creator_share_percent') ?? 0),
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'max_devices' => $request->product_type === 'software' ? max(1, (int) ($request->input('max_devices') ?? 1)) : 1,
            'webhook_url' => $request->product_type === 'software' ? ($request->input('webhook_url') ?: null) : null,
            'webhook_secret' => $request->product_type === 'software' ? ($request->input('webhook_secret') ?: null) : null,
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

        $product = Product::create($data);

        if (! $isFree) {
            $this->syncPackages($product, $request->input('packages', []));
        }

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
            'compare_at_price' => 'nullable|numeric|min:0|gte:price',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'commission_percent_non_owner' => 'required|numeric|min:0|max:100',
            'upline_percent' => 'required|numeric|min:0|max:100',
            'upline_percent_non_owner' => 'required|numeric|min:0|max:100',
            'creator_share_percent' => 'nullable|numeric|min:0|max:100',
            'product_type' => 'required|in:digital,software,free',
            'license_duration' => 'nullable|in:1_month,6_months,1_year,lifetime',
            'max_devices' => 'nullable|integer|min:1|max:100',
            'webhook_url' => 'nullable|url|max:500',
            'webhook_secret' => 'nullable|string|max:128',
            'packages' => 'nullable|array',
            'packages.*.id' => 'nullable|integer|exists:product_packages,id',
            'packages.*.label' => 'nullable|string|max:100',
            'packages.*.duration_type' => 'required_with:packages|in:1_month,6_months,1_year,lifetime',
            'packages.*.price' => 'required_with:packages|numeric|min:0',
            'packages.*.compare_at_price' => 'nullable|numeric|min:0',
            'packages.*.is_active' => 'nullable|boolean',
            'file' => 'nullable|file|max:102400',
            'file_url' => 'nullable|url|max:2048',
            'thumbnail' => 'nullable|image|max:5120',
        ], [
            'compare_at_price.gte' => 'Harga coret harus lebih besar atau sama dengan harga jual.',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'price' => $isFree ? 0 : $request->price,
            'compare_at_price' => $isFree ? null : ($request->input('compare_at_price') ?: null),
            'commission_percent' => $isFree ? 0 : $request->commission_percent,
            'commission_percent_non_owner' => $isFree ? 0 : $request->commission_percent_non_owner,
            'upline_percent' => $isFree ? 0 : $request->upline_percent,
            'upline_percent_non_owner' => $isFree ? 0 : $request->upline_percent_non_owner,
            'creator_share_percent' => $isFree ? 0 : ($request->input('creator_share_percent') ?? 0),
            'product_type' => $request->product_type,
            'license_duration' => $request->product_type === 'software' ? ($request->input('license_duration') ?? 'lifetime') : 'lifetime',
            'max_devices' => $request->product_type === 'software' ? max(1, (int) ($request->input('max_devices') ?? 1)) : 1,
            'webhook_url' => $request->product_type === 'software' ? ($request->input('webhook_url') ?: null) : null,
            'webhook_secret' => $request->product_type === 'software' ? ($request->input('webhook_secret') ?: null) : null,
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

        if ($isFree) {
            $product->packages()->delete();
        } else {
            $this->syncPackages($product, $request->input('packages', []));
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Sync product packages from the form input. The input shape is
     * an associative array keyed by row index, each value containing
     * { id?, label, duration_type, price, compare_at_price?, is_active? }.
     *
     * Behavior:
     * - rows with `id` are updated; rows without `id` are created.
     * - existing packages whose id is missing from input are deleted.
     * - sort_order follows the row order.
     */
    private function syncPackages(Product $product, array $rows): void
    {
        $keptIds = [];
        $sort = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (! isset($row['duration_type']) || ! isset($row['price'])) {
                continue;
            }

            $payload = [
                'label' => isset($row['label']) ? (string) $row['label'] : '',
                'duration_type' => $row['duration_type'],
                'price' => $row['price'],
                'compare_at_price' => ($row['compare_at_price'] ?? null) ?: null,
                'sort_order' => $sort++,
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];

            if (! empty($row['id'])) {
                $pkg = ProductPackage::where('product_id', $product->id)->find($row['id']);
                if ($pkg) {
                    $pkg->update($payload);
                    $keptIds[] = $pkg->id;
                }
            } else {
                $pkg = $product->packages()->create($payload);
                $keptIds[] = $pkg->id;
            }
        }

        $product->packages()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
