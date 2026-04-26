<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = $request->user()
            ->commissions()
            ->with('order.product')
            ->latest()
            ->paginate(15);

        return view('dashboard.commissions', compact('commissions'));
    }
}
