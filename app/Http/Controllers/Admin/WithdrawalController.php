<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transfer_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'transfer_proof.image' => 'Bukti transfer harus berupa gambar.',
            'transfer_proof.max' => 'Ukuran bukti transfer maksimal 4MB.',
        ]);

        $data = ['status' => 'approved'];

        if ($request->hasFile('transfer_proof')) {
            if ($withdrawal->transfer_proof) {
                Storage::disk('public')->delete($withdrawal->transfer_proof);
            }
            $data['transfer_proof'] = $request->file('transfer_proof')->store('transfer-proofs', 'public');
        }

        $withdrawal->update($data);

        return back()->with('success', 'Penarikan berhasil disetujui.'
            . ($request->hasFile('transfer_proof') ? ' Bukti transfer terupload.' : ''));
    }

    /**
     * Upload / ganti bukti transfer untuk penarikan yang sudah disetujui
     * (mis. admin lupa unggah saat approve).
     */
    public function uploadProof(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'transfer_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'transfer_proof.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'transfer_proof.image' => 'Bukti transfer harus berupa gambar.',
            'transfer_proof.max' => 'Ukuran bukti transfer maksimal 4MB.',
        ]);

        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Bukti transfer hanya bisa diunggah untuk penarikan yang sudah disetujui.');
        }

        if ($withdrawal->transfer_proof) {
            Storage::disk('public')->delete($withdrawal->transfer_proof);
        }

        $withdrawal->update([
            'transfer_proof' => $request->file('transfer_proof')->store('transfer-proofs', 'public'),
        ]);

        return back()->with('success', 'Bukti transfer berhasil diperbarui.');
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
