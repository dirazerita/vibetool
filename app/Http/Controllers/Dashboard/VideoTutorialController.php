<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;

class VideoTutorialController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->whereHas('videoTutorials', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['videoTutorials' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->get();

        return view('dashboard.video-tutorials', compact('products'));
    }

    public function show(Product $product)
    {
        $tutorials = $product->videoTutorials()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('dashboard.video-tutorials-show', compact('product', 'tutorials'));
    }
}
