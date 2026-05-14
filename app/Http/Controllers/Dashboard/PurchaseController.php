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
            ->where(function ($query) {
                $query->where('status', 'paid')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                            ->where('payment_method', 'manual');
                    });
            })
            ->latest()
            ->paginate(12);

        return view('dashboard.purchases', compact('purchases'));
    }
}
