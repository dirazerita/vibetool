<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount(['members', 'products'])->latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $members = User::where('role', 'member')->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('title')->get();
        return view('admin.coupons.create', compact('members', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expired_at' => 'nullable|date|after:now',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'is_active' => 'nullable',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_purchase' => $request->min_purchase ?? 0,
            'max_uses' => $request->max_uses,
            'expired_at' => $request->expired_at,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('members')) {
            $coupon->members()->attach($request->members);
        }

        if ($request->filled('products')) {
            $coupon->products()->attach($request->products);
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil ditambahkan.');
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['members', 'products']);
        $usedOrders = \App\Models\Order::where('coupon_code', $coupon->code)
            ->with(['user', 'product'])
            ->latest()
            ->get();

        return view('admin.coupons.show', compact('coupon', 'usedOrders'));
    }

    public function edit(Coupon $coupon)
    {
        $coupon->load(['members', 'products']);
        $members = User::where('role', 'member')->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('title')->get();
        return view('admin.coupons.edit', compact('coupon', 'members', 'products'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expired_at' => 'nullable|date',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'is_active' => 'nullable',
        ]);

        $coupon->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_purchase' => $request->min_purchase ?? 0,
            'max_uses' => $request->max_uses,
            'expired_at' => $request->expired_at,
            'is_active' => $request->boolean('is_active'),
        ]);

        $coupon->members()->sync($request->members ?? []);
        $coupon->products()->sync($request->products ?? []);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil dihapus.');
    }

    public function generateCode()
    {
        $code = strtoupper(Str::random(8));
        while (Coupon::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }
        return response()->json(['code' => $code]);
    }
}
