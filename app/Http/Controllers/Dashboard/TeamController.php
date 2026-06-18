<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $userSales = Order::where('affiliate_id', $user->id)->where('status', 'paid');
        $userTotalSales = $userSales->count();
        $userTotalRevenue = (clone $userSales)->sum('amount');

        $downlines = $user->downlines()
            ->withCount(['affiliateOrders as total_sales' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->withSum(['affiliateOrders as total_revenue' => function ($q) {
                $q->where('status', 'paid');
            }], 'amount')
            ->with(['downlines' => function ($q) {
                $q->withCount(['affiliateOrders as total_sales' => function ($q2) {
                    $q2->where('status', 'paid');
                }])
                ->withSum(['affiliateOrders as total_revenue' => function ($q2) {
                    $q2->where('status', 'paid');
                }], 'amount');
            }])
            ->latest()
            ->get();

        return view('dashboard.team', compact('user', 'downlines', 'userTotalSales', 'userTotalRevenue'));
    }

    /**
     * Detail seorang member tim. Hanya boleh diakses bila member tersebut
     * merupakan downline (keturunan) dari user yang sedang login.
     */
    public function show(Request $request, User $member)
    {
        $viewer = $request->user();

        if (! $this->isDescendantOf($member, $viewer)) {
            abort(403, 'Member ini bukan bagian dari tim Anda.');
        }

        // Penjualan member ini (sebagai affiliator).
        $salesQuery = Order::where('affiliate_id', $member->id)->where('status', 'paid');
        $salesCount = (clone $salesQuery)->count();
        $salesRevenue = (clone $salesQuery)->sum('amount');

        // Pembelian member ini (sebagai pembeli) — produk apa saja yang dibeli.
        $purchases = Order::where('user_id', $member->id)
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->with('product:id,title,slug')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();

        // Komisi yang VIEWER hasilkan dari pembelian member ini.
        $commissionForViewer = $purchases->isEmpty() ? 0 : Commission::where('user_id', $viewer->id)
            ->whereIn('order_id', $purchases->pluck('id'))
            ->sum('amount');

        // Komisi per order (untuk ditampilkan di tiap produk).
        $commissionByOrder = $purchases->isEmpty() ? collect() : Commission::where('user_id', $viewer->id)
            ->whereIn('order_id', $purchases->pluck('id'))
            ->get()
            ->groupBy('order_id');

        // Sub-downline langsung dari member ini.
        $subDownlines = $member->downlines()
            ->withCount(['affiliateOrders as total_sales' => fn ($q) => $q->where('status', 'paid')])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at', 'upline_id']);

        $isDirect = (int) $member->upline_id === (int) $viewer->id;

        return view('dashboard.team-show', [
            'viewer' => $viewer,
            'member' => $member,
            'isDirect' => $isDirect,
            'salesCount' => $salesCount,
            'salesRevenue' => $salesRevenue,
            'purchases' => $purchases,
            'commissionForViewer' => $commissionForViewer,
            'commissionByOrder' => $commissionByOrder,
            'subDownlines' => $subDownlines,
        ]);
    }

    /**
     * Cek apakah $member adalah keturunan (downline langsung/tidak langsung)
     * dari $ancestor dengan menelusuri rantai upline ke atas. Dibatasi
     * kedalaman wajar untuk mencegah loop tak terduga.
     */
    private function isDescendantOf(User $member, User $ancestor): bool
    {
        if ((int) $member->id === (int) $ancestor->id) {
            return false;
        }

        $currentUplineId = $member->upline_id;
        $depth = 0;

        while ($currentUplineId && $depth < 50) {
            if ((int) $currentUplineId === (int) $ancestor->id) {
                return true;
            }

            $parent = User::select('id', 'upline_id')->find($currentUplineId);
            if (! $parent) {
                break;
            }

            $currentUplineId = $parent->upline_id;
            $depth++;
        }

        return false;
    }
}
