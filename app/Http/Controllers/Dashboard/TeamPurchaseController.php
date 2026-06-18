<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use Illuminate\Http\Request;

class TeamPurchaseController extends Controller
{
    /**
     * Analisa pembelian tim: menampilkan downline langsung milik member,
     * mana yang sudah/belum membeli, produk apa yang mereka beli, dan berapa
     * komisi yang dihasilkan untuk member dari pembelian tersebut.
     *
     * Tujuan: membantu member mem-follow-up downline yang belum membeli.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->query('filter'); // null | 'buyers' | 'non_buyers'

        // Definisi "sudah beli": punya minimal 1 order lunas berbayar (amount > 0).
        $paidPurchase = fn ($q) => $q->where('status', 'paid')->where('amount', '>', 0);

        // ID seluruh downline langsung (ringan, hanya id) untuk statistik agregat.
        $allDownlineIds = $user->downlines()->pluck('id');

        // Statistik ringkas (dihitung lewat query, bukan memuat semua relasi).
        $totalDownlines = $allDownlineIds->count();

        $buyerCount = $allDownlineIds->isEmpty() ? 0 : Order::whereIn('user_id', $allDownlineIds)
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->distinct('user_id')
            ->count('user_id');

        $nonBuyerCount = $totalDownlines - $buyerCount;

        // Total komisi yang member dapatkan dari pembelian downline-nya.
        $teamCommission = $allDownlineIds->isEmpty() ? 0 : Commission::where('user_id', $user->id)
            ->whereIn('order_id', Order::whereIn('user_id', $allDownlineIds)
                ->where('status', 'paid')
                ->select('id'))
            ->sum('amount');

        // Daftar downline (dipaginasi) sesuai filter.
        $downlines = $user->downlines()
            ->when($filter === 'buyers', fn ($q) => $q->whereHas('orders', $paidPurchase))
            ->when($filter === 'non_buyers', fn ($q) => $q->whereDoesntHave('orders', $paidPurchase))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Agregasi per-downline HANYA untuk halaman aktif (hindari beban besar).
        $pageIds = collect($downlines->items())->pluck('id');

        $orders = $pageIds->isEmpty() ? collect() : Order::whereIn('user_id', $pageIds)
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->with('product:id,title,slug')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();

        // Komisi member dari order-order tersebut, dikelompokkan per order.
        $commissionByOrder = $orders->isEmpty() ? collect() : Commission::where('user_id', $user->id)
            ->whereIn('order_id', $orders->pluck('id'))
            ->get()
            ->groupBy('order_id');

        $ordersByBuyer = $orders->groupBy('user_id');

        // Tempelkan ringkasan ke tiap downline di halaman ini.
        $downlines->getCollection()->transform(function ($member) use ($ordersByBuyer, $commissionByOrder) {
            $memberOrders = $ordersByBuyer->get($member->id, collect());

            $commission = $memberOrders->sum(
                fn ($order) => optional($commissionByOrder->get($order->id))->sum('amount') ?? 0
            );

            $member->purchase_count = $memberOrders->count();
            $member->total_spent = $memberOrders->sum('amount');
            $member->commission_earned = $commission;
            $member->last_purchase_at = $memberOrders->max('paid_at') ?? $memberOrders->max('created_at');
            $member->purchased_products = $memberOrders->map(fn ($order) => [
                'title' => $order->product->title ?? 'Produk dihapus',
                'amount' => (float) $order->amount,
                'date' => $order->paid_at ?? $order->created_at,
                'my_commission' => (float) (optional($commissionByOrder->get($order->id))->sum('amount') ?? 0),
            ])->values();

            return $member;
        });

        return view('dashboard.team-purchases', [
            'downlines' => $downlines,
            'filter' => $filter,
            'totalDownlines' => $totalDownlines,
            'buyerCount' => $buyerCount,
            'nonBuyerCount' => $nonBuyerCount,
            'teamCommission' => $teamCommission,
        ]);
    }
}
