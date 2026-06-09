<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $commissionsIn = $user->commissions()
            ->where('amount', '>', 0)
            ->where('status', 'approved')
            ->with('order.product:id,title')
            ->orderByDesc('created_at');

        $withdrawalsOut = $user->withdrawals()
            ->orderByDesc('created_at');

        $totalApprovedCommission = $user->commissions()
            ->where('status', 'approved')
            ->sum('amount');
        $totalPendingCommission = $user->commissions()
            ->where('status', 'pending')
            ->sum('amount');
        $totalRejectedCommission = $user->commissions()
            ->where('status', 'rejected')
            ->sum('amount');

        $totalWithdrawn = $user->withdrawals()
            ->where('status', 'approved')
            ->sum('amount');
        $totalPendingWithdrawal = $user->withdrawals()
            ->where('status', 'pending')
            ->sum('amount');

        return view('dashboard.balance', [
            'user' => $user,
            'commissions' => $commissionsIn->paginate(15, ['*'], 'commissions_page'),
            'withdrawals' => $withdrawalsOut->paginate(15, ['*'], 'withdrawals_page'),
            'totalApprovedCommission' => $totalApprovedCommission,
            'totalPendingCommission' => $totalPendingCommission,
            'totalRejectedCommission' => $totalRejectedCommission,
            'totalWithdrawn' => $totalWithdrawn,
            'totalPendingWithdrawal' => $totalPendingWithdrawal,
        ]);
    }
}
