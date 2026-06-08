<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $query = PromoTemplate::query()->with('product:id,title,slug');

        if ($category = $request->string('category')->toString()) {
            if (array_key_exists($category, PromoTemplate::CATEGORIES)) {
                $query->where('category', $category);
            }
        }

        $templates = $query->orderBy('category')
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promo-templates.index', [
            'templates' => $templates,
            'category' => $category ?? '',
            'counts' => [
                'all' => PromoTemplate::count(),
                'member' => PromoTemplate::where('category', PromoTemplate::CATEGORY_MEMBER)->count(),
                'product' => PromoTemplate::where('category', PromoTemplate::CATEGORY_PRODUCT)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.promo-templates.form', [
            'template' => new PromoTemplate(['category' => PromoTemplate::CATEGORY_MEMBER, 'is_active' => true, 'order' => 0]),
            'products' => Product::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        PromoTemplate::create($data);

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil dibuat.');
    }

    public function edit(PromoTemplate $promoTemplate): View
    {
        return view('admin.promo-templates.form', [
            'template' => $promoTemplate,
            'products' => Product::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $data = $this->validateData($request);
        $promoTemplate->update($data);

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil diperbarui.');
    }

    public function destroy(PromoTemplate $promoTemplate): RedirectResponse
    {
        $promoTemplate->delete();

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:'.implode(',', array_keys(PromoTemplate::CATEGORIES))],
            'product_id' => ['nullable', 'exists:products,id'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'title.required' => 'Judul template wajib diisi.',
            'category.required' => 'Pilih kategori (Promo Member atau Promo Produk).',
            'category.in' => 'Kategori tidak valid.',
            'body.required' => 'Isi template wajib diisi.',
            'body.max' => 'Isi template terlalu panjang (maks 5000 karakter).',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['order'] = (int) ($data['order'] ?? 0);
        if ($data['category'] !== PromoTemplate::CATEGORY_PRODUCT) {
            $data['product_id'] = null;
        }

        return $data;
    }
}
