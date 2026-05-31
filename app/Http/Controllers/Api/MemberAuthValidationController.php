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
     * Request: { email, password, product_slug (string|string[]) }
     * Response sukses: { valid: true, user, product, matched_slug }
     * Response gagal: { valid: false, error, message }
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'product_slug' => 'required',
            'product_slug.*' => 'string|max:255',
        ]);

        // Accept product_slug as single string OR array of strings, same as
        // /license/validate. Matches the first active product whose slug is in
        // the list; lets one software validate against multiple product slugs.
        $slugs = $this->normalizeProductSlugs($request->input('product_slug'));

        if (empty($slugs)) {
            return response()->json([
                'valid' => false,
                'error' => 'product_not_found',
                'message' => 'Produk tidak ditemukan atau sudah tidak aktif.',
            ], 404);
        }

        $products = Product::whereIn('slug', $slugs)
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'valid' => false,
                'error' => 'product_not_found',
                'message' => 'Produk tidak ditemukan atau sudah tidak aktif.',
            ], 404);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
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

        // Find the first product (in submitted slug order) the user has paid access to.
        // Ordering by submitted slug list keeps the result deterministic when multiple match.
        $accessibleProductIds = Order::where('user_id', $user->id)
            ->whereIn('product_id', $products->pluck('id'))
            ->where('status', 'paid')
            ->pluck('product_id')
            ->unique();

        $matched = null;
        foreach ($slugs as $slug) {
            $candidate = $products->firstWhere('slug', $slug);
            if ($candidate && $accessibleProductIds->contains($candidate->id)) {
                $matched = $candidate;
                break;
            }
        }

        if (! $matched) {
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
                'id' => $matched->id,
                'title' => $matched->title,
                'slug' => $matched->slug,
                'type' => $matched->product_type,
            ],
            'matched_slug' => $matched->slug,
        ]);
    }

    /**
     * Same logic as LicenseValidationController::normalizeProductSlugs.
     * Kept inline (instead of extracted to a trait) to keep this PR's diff focused.
     */
    private function normalizeProductSlugs(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_string($raw)) {
            $trimmed = trim($raw);
            if (str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $raw = $decoded;
                }
            }
            if (is_string($raw)) {
                return [$trimmed];
            }
        }

        if (! is_array($raw)) {
            return [];
        }

        $slugs = [];
        foreach ($raw as $value) {
            if (! is_string($value)) {
                continue;
            }
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            $slugs[] = $value;
        }

        return array_values(array_unique($slugs));
    }
}
