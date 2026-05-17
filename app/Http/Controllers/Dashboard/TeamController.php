<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $userSales = Order::where('affiliate_id', $user->id)->where('status', 'paid');
        $userTotalSales = $userSales->count();
        $userTotalRevenue = (clone $userSales)->sum('total_price');

        $downlines = $user->downlines()
            ->withCount(['affiliateOrders as total_sales' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->withSum(['affiliateOrders as total_revenue' => function ($q) {
                $q->where('status', 'paid');
            }], 'total_price')
            ->with(['downlines' => function ($q) {
                $q->withCount(['affiliateOrders as total_sales' => function ($q2) {
                    $q2->where('status', 'paid');
                }])
                ->withSum(['affiliateOrders as total_revenue' => function ($q2) {
                    $q2->where('status', 'paid');
                }], 'total_price');
            }])
            ->latest()
            ->get();

        return view('dashboard.team', compact('user', 'downlines', 'userTotalSales', 'userTotalRevenue'));
    }
}
