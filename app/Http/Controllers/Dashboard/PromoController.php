<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PromoTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $category = $request->string('category')->toString();
        if (! array_key_exists($category, PromoTemplate::CATEGORIES)) {
            $category = PromoTemplate::CATEGORY_MEMBER;
        }

        $templates = PromoTemplate::query()
            ->where('is_active', true)
            ->where('category', $category)
            ->with('product:id,title,slug,price,compare_at_price,description')
            ->orderBy('order')
            ->orderByDesc('id')
            ->get();

        $rendered = $templates->map(function (PromoTemplate $t) use ($user) {
            return [
                'id' => $t->id,
                'title' => $t->title,
                'category' => $t->category,
                'product' => $t->product,
                'body' => $t->renderFor($user),
            ];
        });

        return view('dashboard.promo.index', [
            'category' => $category,
            'templates' => $rendered,
            'counts' => [
                'member' => PromoTemplate::where('is_active', true)
                    ->where('category', PromoTemplate::CATEGORY_MEMBER)
                    ->count(),
                'product' => PromoTemplate::where('is_active', true)
                    ->where('category', PromoTemplate::CATEGORY_PRODUCT)
                    ->count(),
            ],
        ]);
    }
}
