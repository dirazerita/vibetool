<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CreatorShareController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('creator')
            ->whereNotNull('created_by')
            ->orderByDesc('created_at');

        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.creator-shares.index', compact('products', 'search'));
    }

    public function update(Request $request, Product $product)
    {
        if (! $product->created_by) {
            return redirect()
                ->route('admin.creator-shares.index')
                ->with('error', 'Produk ini bukan upload member, tidak perlu set creator share.');
        }

        $validated = $request->validate([
            'creator_share_percent' => 'required|numeric|min:0|max:100',
        ]);

        $product->update([
            'creator_share_percent' => $validated['creator_share_percent'],
        ]);

        return redirect()
            ->route('admin.creator-shares.index', $request->only('q', 'page'))
            ->with('success', "Bagian pembuat untuk \"{$product->title}\" berhasil diperbarui menjadi {$validated['creator_share_percent']}%.");
    }
}
