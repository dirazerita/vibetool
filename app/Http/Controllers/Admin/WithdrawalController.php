<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function approve(Withdrawal $withdrawal)
    {
        $withdrawal->update(['status' => 'approved']);
        return back()->with('success', 'Penarikan berhasil disetujui.');
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate(['note' => 'nullable|string']);

        $withdrawal->update([
            'status' => 'rejected',
            'note' => $request->note,
        ]);

        $withdrawal->user->increment('balance', $withdrawal->amount);

        return back()->with('success', 'Penarikan berhasil ditolak dan saldo dikembalikan.');
    }
}
