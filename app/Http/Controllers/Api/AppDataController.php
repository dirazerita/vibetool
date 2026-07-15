<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Data endpoint untuk aplikasi Android native (folder "ANDROID NATIVE").
 * Semua response berbentuk { ok: true, ... } agar mudah diparse Retrofit.
 */
class AppDataController extends Controller
{
    /** Daftar produk marketplace (publik). */
    public function products(): JsonResponse
    {
        $products = Product::with(['landingPage', 'activePackages'])
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->latest()
            ->get()
            ->map(fn (Product $p) => $this->formatProduct($p));

        return response()->json(['ok' => true, 'products' => $products]);
    }

    /** Detail satu produk (publik). */
    public function product(string $slug): JsonResponse
    {
        $product = Product::with(['landingPage', 'activePackages'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->first();

        if (! $product) {
            return response()->json(['ok' => false, 'error' => 'not_found', 'message' => 'Produk tidak ditemukan.'], 404);
        }

        return response()->json(['ok' => true, 'product' => $this->formatProduct($product, detail: true)]);
    }

    /** Ringkasan dashboard member. */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalCommission = (float) Commission::where('user_id', $user->id)->sum('amount');
        $teamCount = $user->downlines()->count();
        $purchaseCount = Order::where('user_id', $user->id)->where('status', 'paid')->count();

        $recentCommissions = Commission::with('order.product:id,title')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Commission $c) => $this->formatCommission($c));

        return response()->json([
            'ok' => true,
            'summary' => [
                'balance' => (float) $user->balance,
                'total_commission' => $totalCommission,
                'team_count' => $teamCount,
                'purchase_count' => $purchaseCount,
                'referral_code' => $user->referral_code,
                'referral_link' => route('home').'?ref='.$user->referral_code,
            ],
            'recent_commissions' => $recentCommissions,
        ]);
    }

    /** Lisensi software milik member. */
    public function licenses(Request $request): JsonResponse
    {
        $licenses = License::with(['product:id,title,slug,max_devices', 'devices'])
            ->where('user_id', $request->user()->id)
            ->orderBy('assigned_at', 'desc')
            ->get()
            ->map(function (License $l) {
                return [
                    'id' => $l->id,
                    'key' => $l->key,
                    'product_title' => $l->product?->title ?? 'Produk telah dihapus',
                    'is_lifetime' => $l->isLifetime(),
                    'is_expired' => $l->isExpired(),
                    'expires_at' => $l->expires_at?->toIso8601String(),
                    'assigned_at' => $l->assigned_at?->toIso8601String(),
                    'max_devices' => max(1, (int) ($l->product?->max_devices ?? 1)),
                    'devices' => $l->devices->map(fn ($d) => [
                        'label' => $d->label,
                        'last_seen_at' => $d->last_seen_at?->toIso8601String(),
                    ])->values(),
                ];
            });

        return response()->json(['ok' => true, 'licenses' => $licenses]);
    }

    /** Reset semua device lisensi milik member (fitur sama dengan web). */
    public function resetLicenseDevices(Request $request, License $license): JsonResponse
    {
        if ((int) $license->user_id !== (int) $request->user()->id) {
            return response()->json(['ok' => false, 'error' => 'forbidden', 'message' => 'Bukan lisensi milikmu.'], 403);
        }

        $deleted = $license->devices()->delete();

        return response()->json(['ok' => true, 'message' => "Berhasil melepas {$deleted} perangkat."]);
    }

    /** Riwayat komisi member. */
    public function commissions(Request $request): JsonResponse
    {
        $commissions = Commission::with('order.product:id,title')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);

        return response()->json([
            'ok' => true,
            'commissions' => collect($commissions->items())->map(fn (Commission $c) => $this->formatCommission($c)),
            'has_more' => $commissions->hasMorePages(),
            'page' => $commissions->currentPage(),
        ]);
    }

    /** Daftar tim / downline member. */
    public function team(Request $request): JsonResponse
    {
        $team = $request->user()->downlines()
            ->select('id', 'name', 'email', 'created_at', 'status')
            ->latest()
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'status' => $m->status,
                'joined_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['ok' => true, 'team' => $team]);
    }

    /** Riwayat pembelian member. */
    public function purchases(Request $request): JsonResponse
    {
        $orders = Order::with('product:id,title,slug')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'product_title' => $o->product?->title ?? '—',
                'amount' => (float) $o->amount,
                'status' => $o->status,
                'created_at' => $o->created_at->toIso8601String(),
                'download_url' => ($o->status === 'paid' && $o->download_token) ? route('download', $o->download_token) : null,
            ]);

        return response()->json(['ok' => true, 'purchases' => $orders]);
    }

    /**
     * Link checkout sekali pakai: signed URL yang otomatis melogin user di web
     * lalu redirect ke halaman checkout produk. Pembayaran (Xendit/Pakasir)
     * memang berbasis web redirect, jadi app membukanya di Custom Tabs.
     */
    public function checkoutLink(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->first();

        if (! $product) {
            return response()->json(['ok' => false, 'error' => 'not_found', 'message' => 'Produk tidak ditemukan.'], 404);
        }

        $url = URL::temporarySignedRoute('app.autologin', now()->addMinutes(15), [
            'user' => $request->user()->id,
            'slug' => $product->slug,
        ]);

        return response()->json(['ok' => true, 'checkout_url' => $url]);
    }

    private function formatProduct(Product $p, bool $detail = false): array
    {
        $thumb = null;
        if ($p->thumbnail) {
            $thumb = asset('storage/'.$p->thumbnail);
        } elseif ($p->landingPage && $p->landingPage->hero_image) {
            $thumb = asset('storage/'.$p->landingPage->hero_image);
        }

        $hasPackages = $p->hasPackages();
        $compareAt = ! $hasPackages && $p->compare_at_price !== null && (float) $p->compare_at_price > (float) $p->price
            ? (float) $p->compare_at_price
            : null;

        $data = [
            'id' => $p->id,
            'slug' => $p->slug,
            'title' => $p->title,
            'description' => (string) $p->description,
            'price' => (float) $p->startingPrice(),
            'compare_at_price' => $compareAt,
            'is_free' => $p->isFree(),
            'has_packages' => $hasPackages,
            'product_type' => $p->product_type,
            'thumbnail' => $thumb,
            'web_url' => route('product.show', $p->slug),
        ];

        if ($detail) {
            $data['packages'] = $p->activePackages->map(fn ($pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'price' => (float) $pkg->price,
                'duration_type' => $pkg->duration_type,
            ])->values();
        }

        return $data;
    }

    private function formatCommission(Commission $c): array
    {
        return [
            'id' => $c->id,
            'type' => $c->type,
            'amount' => (float) $c->amount,
            'status' => $c->status,
            'product_title' => $c->order?->product?->title ?? '—',
            'created_at' => $c->created_at->toIso8601String(),
        ];
    }
}
