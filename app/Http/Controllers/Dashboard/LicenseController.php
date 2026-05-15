<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $licenses = License::with(['product.landingPage', 'order'])
            ->where('user_id', $userId)
            ->orderBy('assigned_at', 'desc')
            ->get();

        $pendingOrders = Order::with('product.landingPage')
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->whereHas('product', function ($q) {
                $q->where('product_type', 'software');
            })
            ->whereDoesntHave('license')
            ->latest()
            ->get();

        return view('dashboard.licenses', compact('licenses', 'pendingOrders'));
    }
}
