<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberAuthValidationController extends Controller
{
    /**
     * Validasi kredensial member untuk akses Produk Gratis (software).
     *
     * Request: { email, password, product_slug }
     * Response sukses: { valid: true, user, product }
     * Response gagal: { valid: false, error, message }
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'product_slug' => 'required|string',
        ]);

        $product = Product::where('slug', $request->input('product_slug'))
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'valid' => false,
                'error' => 'product_not_found',
                'message' => 'Produk tidak ditemukan atau sudah tidak aktif.',
            ], 404);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'valid' => false,
                'error' => 'invalid_credentials',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (($user->status ?? 'active') === 'pending') {
            return response()->json([
                'valid' => false,
                'error' => 'account_inactive',
                'message' => 'Akun belum diaktifkan oleh admin.',
            ], 403);
        }

        $hasAccess = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'paid')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'valid' => false,
                'error' => 'no_access',
                'message' => 'Akun ini belum klaim produk gratis ini. Silakan klaim dulu di dashboard PRODIG.',
            ], 403);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Akses valid.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'type' => $product->product_type,
            ],
        ]);
    }
}
