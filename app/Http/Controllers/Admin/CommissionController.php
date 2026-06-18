<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\Withdrawal;

class CommissionController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')
            ->whereHas('commissions', fn ($q) => $q->where('amount', '>', 0))
            ->withCount('commissions')
            ->withSum('commissions as total_commission', 'amount')
            ->withSum([
                'commissions as direct_commission' => fn ($q) => $q->where('type', 'direct'),
            ], 'amount')
            ->withSum([
                'commissions as upline_commission' => fn ($q) => $q->where('type', 'upline'),
            ], 'amount')
            ->withSum([
                'commissions as creator_commission' => fn ($q) => $q->where('type', 'creator'),
            ], 'amount')
            ->withSum([
                // "Dibayarkan" = total penarikan yang sudah DISETUJUI admin
                // (uang yang benar-benar cair ke rekening member).
                'withdrawals as paid_out' => fn ($q) => $q->where('status', 'approved'),
            ], 'amount')
            ->orderByDesc('total_commission')
            ->paginate(20);

        $totalPaidOut = (float) Withdrawal::where('status', 'approved')->sum('amount');
        $totalPendingPayout = (float) Withdrawal::where('status', 'pending')->sum('amount');

        $summary = [
            'total_members' => User::where('role', 'member')->whereHas('commissions', fn ($q) => $q->where('amount', '>', 0))->count(),
            'total_commission' => (float) Commission::sum('amount'),
            'total_direct' => (float) Commission::where('type', 'direct')->sum('amount'),
            'total_upline' => (float) Commission::where('type', 'upline')->sum('amount'),
            'total_creator' => (float) Commission::where('type', 'creator')->sum('amount'),
            'total_paid_out' => $totalPaidOut,
            'total_pending_payout' => $totalPendingPayout,
        ];

        return view('admin.commissions.index', compact('members', 'summary'));
    }

    public function show(User $user)
    {
        if ($user->role !== 'member') {
            abort(404);
        }

        $commissions = $user->commissions()
            ->where('amount', '>', 0)
            ->with(['order.product', 'order.user'])
            ->latest()
            ->paginate(25);

        // Riwayat pembayaran komisi ke member ini (penarikan).
        $payouts = $user->withdrawals()
            ->latest()
            ->get();

        $stats = [
            'total' => (float) $user->commissions()->sum('amount'),
            'direct' => (float) $user->commissions()->where('type', 'direct')->sum('amount'),
            'upline' => (float) $user->commissions()->where('type', 'upline')->sum('amount'),
            'creator' => (float) $user->commissions()->where('type', 'creator')->sum('amount'),
            'count' => (int) $user->commissions()->count(),
            // Sudah dibayarkan = penarikan disetujui; menunggu = penarikan pending.
            'paid_out' => (float) $user->withdrawals()->where('status', 'approved')->sum('amount'),
            'pending_payout' => (float) $user->withdrawals()->where('status', 'pending')->sum('amount'),
        ];

        return view('admin.commissions.show', compact('user', 'commissions', 'stats', 'payouts'));
    }
}
