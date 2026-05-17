<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberCommission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class MemberCommissionController extends Controller
{
    public function index()
    {
        $memberCommissions = MemberCommission::with(['user', 'product'])
            ->latest()
            ->paginate(20);

        return view('admin.member-commissions.index', compact('memberCommissions'));
    }

    public function create()
    {
        $members = User::where('role', 'member')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('title')
            ->get();

        return view('admin.member-commissions.create', compact('members', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'upline_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $skipped = [];
        $created = 0;

        foreach ($request->product_ids as $productId) {
            $exists = MemberCommission::where('user_id', $request->user_id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                $product = Product::find($productId);
                $skipped[] = $product ? $product->title : $productId;
                continue;
            }

            MemberCommission::create([
                'user_id' => $request->user_id,
                'product_id' => $productId,
                'commission_percent' => $request->commission_percent,
                'upline_percent' => $request->upline_percent,
            ]);
            $created++;
        }

        $message = "Komisi khusus berhasil ditambahkan untuk {$created} produk.";
        if (count($skipped) > 0) {
            $message .= ' Dilewati karena sudah ada: ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('admin.member-commissions.index')->with('success', $message);
    }

    public function edit(MemberCommission $memberCommission)
    {
        $members = User::where('role', 'member')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('title')
            ->get();

        return view('admin.member-commissions.edit', compact('memberCommission', 'members', 'products'));
    }

    public function update(Request $request, MemberCommission $memberCommission)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'upline_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $exists = MemberCommission::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->where('id', '!=', $memberCommission->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Komisi khusus untuk member dan produk ini sudah ada.');
        }

        $memberCommission->update([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'commission_percent' => $request->commission_percent,
            'upline_percent' => $request->upline_percent,
        ]);

        return redirect()->route('admin.member-commissions.index')->with('success', 'Komisi khusus berhasil diperbarui.');
    }

    public function destroy(MemberCommission $memberCommission)
    {
        $memberCommission->delete();

        return redirect()->route('admin.member-commissions.index')->with('success', 'Komisi khusus berhasil dihapus.');
    }
}
