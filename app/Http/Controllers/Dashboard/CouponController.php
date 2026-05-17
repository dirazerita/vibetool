<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $assignedCoupons = $user->coupons()
            ->with('products')
            ->orderBy('coupon_members.created_at', 'desc')
            ->get();

        $globalCoupons = Coupon::where('is_active', true)
            ->whereDoesntHave('members')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->with('products')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.coupons', compact('user', 'assignedCoupons', 'globalCoupons'));
    }
}
