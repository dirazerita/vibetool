<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $downlines = $request->user()
            ->downlines()
            ->withCount(['affiliateOrders as total_sales' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->latest()
            ->paginate(15);

        return view('dashboard.team', compact('downlines'));
    }
}
