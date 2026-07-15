<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Coupon;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\Withdrawal;
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

    /**
     * Kupon milik member (assigned) + kupon global aktif — mirror
     * Dashboard\CouponController@index.
     */
    public function coupons(Request $request): JsonResponse
    {
        $user = $request->user();

        $assigned = $user->coupons()
            ->with('products:id,title')
            ->orderBy('coupon_members.created_at', 'desc')
            ->get();

        $global = Coupon::where('is_active', true)
            ->whereDoesntHave('members')
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->with('products:id,title')
            ->orderBy('created_at', 'desc')
            ->get();

        $format = function (Coupon $c) {
            $expired = $c->expired_at !== null && $c->expired_at->isPast();
            $usedUp = $c->max_uses !== null && $c->used_count >= $c->max_uses;

            return [
                'id' => $c->id,
                'code' => $c->code,
                'discount_type' => $c->discount_type,
                'discount_value' => (float) $c->discount_value,
                'expired_at' => $c->expired_at?->toIso8601String(),
                'is_usable' => (bool) $c->is_active && ! $expired && ! $usedUp,
                'products' => $c->products->pluck('title')->values(),
            ];
        };

        return response()->json([
            'ok' => true,
            'assigned' => $assigned->map($format)->values(),
            'global' => $global->map($format)->values(),
        ]);
    }

    /**
     * Analisa pembelian tim — versi ringkas dari
     * Dashboard\TeamPurchaseController@index untuk aplikasi.
     */
    public function teamPurchases(Request $request): JsonResponse
    {
        $user = $request->user();

        $downlines = $user->downlines()
            ->select('id', 'name', 'created_at')
            ->orderBy('name')
            ->limit(200)
            ->get();

        $ids = $downlines->pluck('id');

        $orderTotals = $ids->isEmpty() ? collect() : Order::whereIn('user_id', $ids)
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->selectRaw('user_id, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Komisi yang SAYA dapatkan dari order tiap downline.
        $myCommissions = $ids->isEmpty() ? collect() : Commission::where('user_id', $user->id)
            ->whereHas('order', fn ($q) => $q->whereIn('user_id', $ids)->where('status', 'paid'))
            ->with('order:id,user_id')
            ->get()
            ->groupBy(fn ($c) => $c->order?->user_id);

        $team = $downlines->map(function ($d) use ($orderTotals, $myCommissions) {
            $orders = $orderTotals->get($d->id);
            $commission = collect($myCommissions->get($d->id, []))->sum('amount');

            return [
                'id' => $d->id,
                'name' => $d->name,
                'joined_at' => $d->created_at->toIso8601String(),
                'has_purchased' => $orders !== null,
                'purchase_count' => (int) ($orders->cnt ?? 0),
                'total_spent' => (float) ($orders->total ?? 0),
                'my_commission' => (float) $commission,
            ];
        });

        return response()->json([
            'ok' => true,
            'stats' => [
                'total' => $downlines->count(),
                'buyers' => $orderTotals->count(),
                'non_buyers' => $downlines->count() - $orderTotals->count(),
                'team_commission' => (float) $team->sum('my_commission'),
            ],
            'team' => $team->values(),
        ]);
    }

    /**
     * Riwayat penarikan + info kesiapan (saldo, bank, verifikasi email).
     */
    public function withdrawals(Request $request): JsonResponse
    {
        $user = $request->user();

        $withdrawals = $user->withdrawals()->latest()->limit(50)->get()->map(fn (Withdrawal $w) => [
            'id' => $w->id,
            'amount' => (float) $w->amount,
            'status' => $w->status,
            'bank_name' => $w->bank_name,
            'bank_account' => $w->bank_account,
            'note' => $w->note,
            'created_at' => $w->created_at->toIso8601String(),
        ]);

        return response()->json([
            'ok' => true,
            'balance' => (float) $user->balance,
            'min_amount' => 50000,
            'email_verified' => (bool) $user->email_verified_at,
            'bank_filled' => (bool) ($user->bank_name && $user->bank_account),
            'bank_name' => $user->bank_name,
            'bank_account' => $user->bank_account,
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Ajukan penarikan — validasi identik dengan
     * Dashboard\WithdrawalController@store.
     */
    public function storeWithdrawal(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
        ]);

        $user = $request->user();

        if (! $user->email_verified_at) {
            return response()->json(['ok' => false, 'error' => 'email_unverified', 'message' => 'Verifikasi email dulu sebelum menarik komisi (menu Verifikasi Email).'], 422);
        }

        if ($request->amount > $user->balance) {
            return response()->json(['ok' => false, 'error' => 'insufficient_balance', 'message' => 'Saldo tidak mencukupi.'], 422);
        }

        if (! $user->bank_name || ! $user->bank_account) {
            return response()->json(['ok' => false, 'error' => 'bank_missing', 'message' => 'Lengkapi informasi bank di menu Pengaturan dulu.'], 422);
        }

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_name' => $user->bank_name,
            'bank_account' => $user->bank_account,
        ]);

        $user->decrement('balance', $request->amount);

        return response()->json(['ok' => true, 'message' => 'Permintaan penarikan berhasil diajukan.']);
    }

    /**
     * Link web dashboard sekali pakai: signed URL yang melogin user lalu
     * membuka halaman dashboard web (menu yang belum ada versi native-nya —
     * Kuponku, Penarikan, Pesan, Page Builder, dll). Path ber-whitelist
     * (dashboard/...) — divalidasi lagi di route app.autologin.
     */
    public function webLink(Request $request): JsonResponse
    {
        $to = ltrim((string) $request->query('to'), '/');

        $allowed = $to === 'dashboard' || str_starts_with($to, 'dashboard/');
        if (! $allowed) {
            return response()->json(['ok' => false, 'error' => 'invalid_path', 'message' => 'Path tidak diizinkan.'], 422);
        }

        $url = URL::temporarySignedRoute('app.autologin', now()->addMinutes(15), [
            'user' => $request->user()->id,
            'to' => $to,
        ]);

        return response()->json(['ok' => true, 'url' => $url]);
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
