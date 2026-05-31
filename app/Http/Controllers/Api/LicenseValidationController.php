<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseValidationController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'product_slug' => 'nullable',
            'product_slug.*' => 'string|max:255',
            'device_fingerprint' => 'nullable|string|max:128',
            'device_label' => 'nullable|string|max:64',
        ]);

        // Accept product_slug as either a single string OR an array of strings.
        // Lets one software validate against multiple product slugs (e.g. a Suite
        // app that accepts both "tools-basic" and "tools-pro" licenses).
        $slugs = $this->normalizeProductSlugs($request->input('product_slug'));

        $query = License::with(['product:id,title,slug,product_type,max_devices', 'user:id,name,email'])
            ->where('key', $request->input('key'))
            ->whereNotNull('order_id');

        if (! empty($slugs)) {
            $query->whereHas('product', function ($q) use ($slugs) {
                $q->whereIn('slug', $slugs);
            });
        }

        $license = $query->first();

        if (! $license) {
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

        $fingerprint = trim((string) $request->input('device_fingerprint', ''));

        if ($fingerprint !== '') {
            $registration = $this->registerOrTouchDevice(
                $license,
                $fingerprint,
                $request->input('device_label'),
                $request->ip(),
                substr((string) $request->userAgent(), 0, 255)
            );

            if (! $registration['ok']) {
                return response()->json([
                    'valid' => false,
                    'error' => 'device_limit_exceeded',
                    'message' => sprintf(
                        'Lisensi ini sudah dipakai di %d device (batas maksimum). Hubungi admin untuk reset device kalau ganti perangkat.',
                        $registration['current_count']
                    ),
                    'license' => $this->formatLicense($license),
                    'devices' => $registration['devices'],
                    'max_devices' => $registration['max_devices'],
                ], 403);
            }

            $license->setRelation('devices', $license->devices()->orderBy('first_seen_at')->get());

            return response()->json([
                'valid' => true,
                'message' => 'Lisensi valid.',
                'license' => $this->formatLicense($license),
                'device' => $registration['device'],
                'max_devices' => $registration['max_devices'],
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Lisensi valid.',
            'license' => $this->formatLicense($license),
        ]);
    }

    /**
     * Register device baru atau touch existing device.
     *
     * Return array:
     *   - ok: bool — false kalau device baru tapi sudah mencapai max_devices
     *   - device: array — info device yang baru saja di-register/touch (kalau ok)
     *   - devices: array — list device yang sudah terdaftar (kalau !ok)
     *   - current_count: int
     *   - max_devices: int
     */
    private function registerOrTouchDevice(
        License $license,
        string $fingerprint,
        ?string $label,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $maxDevices = max(1, (int) ($license->product?->max_devices ?? 1));

        $existing = LicenseDevice::where('license_id', $license->id)
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($existing) {
            $existing->fill([
                'last_seen_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
            if ($label !== null && $label !== '' && $existing->label !== $label) {
                $existing->label = $label;
            }
            $existing->save();

            return [
                'ok' => true,
                'device' => $this->formatDevice($existing),
                'max_devices' => $maxDevices,
            ];
        }

        $currentCount = LicenseDevice::where('license_id', $license->id)->count();

        if ($currentCount >= $maxDevices) {
            $devices = LicenseDevice::where('license_id', $license->id)
                ->orderBy('first_seen_at')
                ->get()
                ->map(fn ($d) => $this->formatDevice($d))
                ->toArray();

            return [
                'ok' => false,
                'devices' => $devices,
                'current_count' => $currentCount,
                'max_devices' => $maxDevices,
            ];
        }

        $device = LicenseDevice::create([
            'license_id' => $license->id,
            'fingerprint' => $fingerprint,
            'label' => $label !== '' ? $label : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        return [
            'ok' => true,
            'device' => $this->formatDevice($device),
            'max_devices' => $maxDevices,
        ];
    }

    /**
     * Normalize the incoming product_slug input into a deduped list of slug strings.
     * Accepts:
     *   - null / missing / empty string  → []  (no filter)
     *   - single string "foo"            → ['foo']
     *   - array ["foo", "bar"]           → ['foo', 'bar']
     *   - JSON-encoded array string '["foo","bar"]' (defensive — some clients
     *     can't easily send arrays in form-encoded POST and fall back to JSON-in-string)
     */
    private function normalizeProductSlugs(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_string($raw)) {
            $trimmed = trim($raw);
            // Try parse as JSON array first (e.g. '["a","b"]')
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

    private function formatLicense(License $license): array
    {
        return [
            'key' => $license->key,
            'product' => $license->product ? [
                'id' => $license->product->id,
                'title' => $license->product->title,
                'slug' => $license->product->slug,
                'max_devices' => max(1, (int) ($license->product->max_devices ?? 1)),
            ] : null,
            // Convenience: top-level matched_slug equals license.product.slug. Useful when
            // the caller sent multiple product_slug candidates and wants to know which
            // one this license belongs to without digging into the nested product object.
            'matched_slug' => $license->product?->slug,
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

    private function formatDevice(LicenseDevice $device): array
    {
        return [
            'fingerprint' => $device->fingerprint,
            'label' => $device->label,
            'first_seen_at' => $device->first_seen_at?->toIso8601String(),
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ];
    }
}
