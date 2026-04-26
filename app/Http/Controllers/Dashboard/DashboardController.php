<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalCommissions = $user->commissions()->sum('amount');
        $pendingCommissions = $user->commissions()->where('status', 'pending')->sum('amount');
        $totalDownlines = $user->downlines()->count();
        $totalOrders = $user->affiliateOrders()->where('status', 'paid')->count();

        return view('dashboard.index', compact('user', 'totalCommissions', 'pendingCommissions', 'totalDownlines', 'totalOrders'));
    }
}
