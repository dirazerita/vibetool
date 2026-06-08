<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $downlineQuery = Order::query()
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->where(function ($q) use ($user) {
                $q->where('affiliate_id', $user->id)
                    ->orWhere('upline_id', $user->id);
            });

        $downlineSales = (clone $downlineQuery)
            ->with(['product:id,title,slug', 'user:id,name,email', 'affiliate:id,name'])
            ->latest()
            ->paginate(15, ['*'], 'downline_page');

        $downlineCount = (clone $downlineQuery)->count();
        $downlineRevenue = (clone $downlineQuery)->sum('amount');
        $downlineCommission = Commission::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['direct', 'upline'])
            ->whereIn('order_id', (clone $downlineQuery)->select('id'))
            ->sum('amount');

        $isVendor = $user->canUploadProduct();
        $externalSales = null;
        $externalCount = 0;
        $externalRevenue = 0;
        $externalCommission = 0;

        if ($isVendor) {
            $ownProductIds = $user->createdProducts()->pluck('id');

            if ($ownProductIds->isNotEmpty()) {
                $externalQuery = Order::query()
                    ->where('status', 'paid')
                    ->where('amount', '>', 0)
                    ->whereIn('product_id', $ownProductIds)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('affiliate_id')
                            ->orWhere('affiliate_id', '!=', $user->id);
                    })
                    ->where(function ($q) use ($user) {
                        $q->whereNull('upline_id')
                            ->orWhere('upline_id', '!=', $user->id);
                    });

                $externalSales = (clone $externalQuery)
                    ->with(['product:id,title,slug', 'user:id,name,email', 'affiliate:id,name'])
                    ->latest()
                    ->paginate(15, ['*'], 'external_page');

                $externalCount = (clone $externalQuery)->count();
                $externalRevenue = (clone $externalQuery)->sum('amount');
                $externalCommission = Commission::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'creator')
                    ->whereIn('order_id', (clone $externalQuery)->select('id'))
                    ->sum('amount');
            }
        }

        return view('dashboard.sales', [
            'user' => $user,
            'isVendor' => $isVendor,
            'downlineSales' => $downlineSales,
            'downlineCount' => $downlineCount,
            'downlineRevenue' => $downlineRevenue,
            'downlineCommission' => $downlineCommission,
            'externalSales' => $externalSales,
            'externalCount' => $externalCount,
            'externalRevenue' => $externalRevenue,
            'externalCommission' => $externalCommission,
            'totalSales' => $downlineCount + $externalCount,
            'totalRevenue' => $downlineRevenue + $externalRevenue,
            'totalCommission' => $downlineCommission + $externalCommission,
        ]);
    }
}
