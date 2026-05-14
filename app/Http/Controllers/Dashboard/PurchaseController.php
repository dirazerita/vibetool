<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Order::with(['product.landingPage'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'paid')
            ->latest()
            ->paginate(12);

        return view('dashboard.purchases', compact('purchases'));
    }
}
