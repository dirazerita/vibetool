<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseValidationController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'product_slug' => 'nullable|string',
        ]);

        $query = License::with(['product:id,title,slug,product_type', 'user:id,name,email'])
            ->where('key', $request->input('key'))
            ->whereNotNull('order_id');

        if ($request->filled('product_slug')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('slug', $request->input('product_slug'));
            });
        }

        $license = $query->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'license_not_found',
                'message' => 'Kunci lisensi tidak ditemukan atau belum dialokasikan.',
            ], 404);
        }

        if ($license->isExpired()) {
            return response()->json([
                'valid' => false,
                'error' => 'license_expired',
                'message' => 'Lisensi sudah kedaluwarsa.',
                'license' => $this->formatLicense($license),
            ], 403);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Lisensi valid.',
            'license' => $this->formatLicense($license),
        ]);
    }

    private function formatLicense(License $license): array
    {
        return [
            'key' => $license->key,
            'product' => $license->product ? [
                'id' => $license->product->id,
                'title' => $license->product->title,
                'slug' => $license->product->slug,
            ] : null,
            'user' => $license->user ? [
                'id' => $license->user->id,
                'name' => $license->user->name,
                'email' => $license->user->email,
            ] : null,
            'assigned_at' => $license->assigned_at?->toIso8601String(),
            'expires_at' => $license->expires_at?->toIso8601String(),
            'is_lifetime' => $license->isLifetime(),
        ];
    }
}
