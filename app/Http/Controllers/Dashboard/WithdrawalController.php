<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = $request->user()
            ->withdrawals()
            ->latest()
            ->paginate(15);

        $balance = $request->user()->balance;

        return view('dashboard.withdrawals', compact('withdrawals', 'balance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
        ]);

        $user = $request->user();

        if ($request->amount > $user->balance) {
            return back()->with('error', 'Saldo tidak mencukupi.');
        }

        if (!$user->bank_name || !$user->bank_account) {
            return back()->with('error', 'Lengkapi informasi bank di halaman pengaturan terlebih dahulu.');
        }

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_name' => $user->bank_name,
            'bank_account' => $user->bank_account,
        ]);

        $user->decrement('balance', $request->amount);

        return back()->with('success', 'Permintaan penarikan berhasil diajukan.');
    }
}
