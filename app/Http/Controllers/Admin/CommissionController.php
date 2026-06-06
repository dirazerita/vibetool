<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;

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
            ->orderByDesc('total_commission')
            ->paginate(20);

        $summary = [
            'total_members' => User::where('role', 'member')->whereHas('commissions', fn ($q) => $q->where('amount', '>', 0))->count(),
            'total_commission' => (float) Commission::sum('amount'),
            'total_direct' => (float) Commission::where('type', 'direct')->sum('amount'),
            'total_upline' => (float) Commission::where('type', 'upline')->sum('amount'),
            'total_creator' => (float) Commission::where('type', 'creator')->sum('amount'),
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

        $stats = [
            'total' => (float) $user->commissions()->sum('amount'),
            'direct' => (float) $user->commissions()->where('type', 'direct')->sum('amount'),
            'upline' => (float) $user->commissions()->where('type', 'upline')->sum('amount'),
            'creator' => (float) $user->commissions()->where('type', 'creator')->sum('amount'),
            'count' => (int) $user->commissions()->count(),
        ];

        return view('admin.commissions.show', compact('user', 'commissions', 'stats'));
    }
}
