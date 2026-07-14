<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $licenses = License::with(['product.landingPage', 'order', 'devices' => function ($q) {
            $q->orderBy('first_seen_at');
        }])
            ->where('user_id', $userId)
            ->orderBy('assigned_at', 'desc')
            ->get();

        $pendingOrders = Order::with('product.landingPage')
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->whereHas('product', function ($q) {
                $q->where('product_type', 'software');
            })
            ->whereDoesntHave('license')
            ->latest()
            ->get();

        return view('dashboard.licenses', compact('licenses', 'pendingOrders'));
    }

    /**
     * Reset (lepas) semua device yang terikat ke lisensi milik member.
     *
     * Setelah device dihapus, slot device kembali kosong sehingga member bisa
     * mengaktifkan lisensinya di PC/perangkat lain. Hanya boleh dilakukan oleh
     * pemilik lisensi itu sendiri.
     */
    public function resetDevices(Request $request, License $license)
    {
        abort_unless((int) $license->user_id === (int) $request->user()->id, 403);

        $deleted = $license->devices()->delete();

        $message = $deleted > 0
            ? "Berhasil melepas {$deleted} perangkat. Lisensi kini bisa diaktifkan di perangkat lain."
            : 'Tidak ada perangkat yang terkoneksi. Lisensi sudah bisa dipakai di perangkat mana pun.';

        return redirect()->route('dashboard.licenses')->with('success', $message);
    }
}
