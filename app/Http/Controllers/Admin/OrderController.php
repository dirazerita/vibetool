<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'product', 'affiliate'])
            ->latest()
            ->paginate(15);

        return view('admin.orders', compact('orders'));
    }
}
