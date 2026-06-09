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
            ->where('approval_status', PromoTemplate::STATUS_APPROVED)
            ->where('category', $category)
            ->with(['product:id,title,slug,price,compare_at_price,description', 'media'])
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
                'media' => $t->media->map(fn ($m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'url' => $m->url(),
                    'name' => $m->original_name,
                    'mime' => $m->mime,
                    'size' => $m->humanSize(),
                ])->values()->all(),
            ];
        });

        return view('dashboard.promo.index', [
            'category' => $category,
            'templates' => $rendered,
            'counts' => [
                'member' => PromoTemplate::where('is_active', true)
                    ->where('approval_status', PromoTemplate::STATUS_APPROVED)
                    ->where('category', PromoTemplate::CATEGORY_MEMBER)
                    ->count(),
                'product' => PromoTemplate::where('is_active', true)
                    ->where('approval_status', PromoTemplate::STATUS_APPROVED)
                    ->where('category', PromoTemplate::CATEGORY_PRODUCT)
                    ->count(),
            ],
        ]);
    }
}
