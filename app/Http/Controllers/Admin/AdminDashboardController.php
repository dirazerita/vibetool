<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Withdrawal;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalMembers = User::where('role', 'member')->count();
        $totalProducts = Product::count();
        $totalOrders = Order::where('status', 'paid')->count();
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $totalCommissions = Commission::sum('amount');
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $recentOrders = Order::with(['user', 'product'])->latest()->take(5)->get();

        return view('admin.index', compact(
            'totalMembers', 'totalProducts', 'totalOrders',
            'totalRevenue', 'totalCommissions', 'pendingWithdrawals', 'recentOrders'
        ));
    }
}
