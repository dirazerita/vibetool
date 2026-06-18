<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');

        $query = Order::with(['user', 'product', 'affiliate', 'uplineUser'])->latest();

        // Filter "perlu koreksi komisi": pesanan lunas yang memakai kupon tapi
        // affiliatornya kosong — kasus komisi kupon yang tidak ter-atribusi.
        if ($filter === 'needs_attribution') {
            $query->whereNull('affiliate_id')
                ->whereNotNull('coupon_code')
                ->where('coupon_code', '!=', '')
                ->where('status', 'paid');
        }

        $orders = $query->paginate(15)->withQueryString();

        // Saran 1-klik: untuk pesanan berkupon tanpa affiliator, tentukan
        // pemilik kupon yang layak dijadikan affiliator.
        $couponSuggestions = $this->buildCouponOwnerSuggestions($orders->getCollection());

        // Jumlah pesanan yang perlu koreksi (untuk badge filter).
        $needsAttributionCount = Order::whereNull('affiliate_id')
            ->whereNotNull('coupon_code')
            ->where('coupon_code', '!=', '')
            ->where('status', 'paid')
            ->count();

        return view('admin.orders', compact('orders', 'filter', 'couponSuggestions', 'needsAttributionCount'));
    }

    /**
     * Endpoint JSON untuk pencarian member (dipakai dropdown affiliator yang
     * dimuat lazy di modal). Membatasi hasil agar halaman tidak berat.
     */
    public function searchMembers(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $exclude = (int) $request->query('exclude', 0);

        $members = User::where('role', '!=', 'admin')
            ->when($exclude > 0, fn ($query) => $query->where('id', '!=', $exclude))
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'email']);

        return response()->json($members);
    }

    public function markPaid(Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Pesanan ini tidak dalam status menunggu pembayaran.');
        }

        $activatedMember = false;

        if ($order->user && $order->user->status !== 'active') {
            $order->user->update(['status' => 'active']);
            $order->setRelation('user', $order->user->fresh());
            $activatedMember = true;
        }

        $paymentService->markAsPaid($order);

        $message = 'Pesanan #' . $order->id . ' berhasil ditandai lunas. Komisi sudah diproses.';

        if ($activatedMember && $order->user) {
            $message = 'Pesanan #' . $order->id . ' berhasil ditandai lunas dan member "' . $order->user->name . '" otomatis diaktifkan. Komisi sudah diproses.';
        }

        return back()->with('success', $message);
    }

    /**
     * Ganti affiliator sebuah pesanan. Upline-nya otomatis ikut diatur dari
     * upline_id affiliator yang dipilih, dan komisi dihitung ulang bila order
     * sudah lunas.
     */
    public function updateAffiliate(Request $request, Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        $validated = $request->validate([
            'affiliate_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'affiliate_id.exists' => 'Member affiliator yang dipilih tidak ditemukan.',
        ]);

        $affiliateId = $validated['affiliate_id'] ?? null;

        if ($affiliateId && (int) $affiliateId === (int) $order->user_id) {
            return back()->with('error', 'Affiliator tidak boleh sama dengan pembeli.');
        }

        $product = $order->product;
        if ($affiliateId && $product && $product->created_by && (int) $affiliateId === (int) $product->created_by) {
            return back()->with('error', 'Pembuat produk tidak bisa menjadi affiliator untuk produknya sendiri.');
        }

        $warnings = $paymentService->reassignAffiliate($order, $affiliateId ? (int) $affiliateId : null);

        $order->refresh()->load(['affiliate', 'uplineUser']);

        if ($order->affiliate_id) {
            $message = 'Affiliator pesanan #' . $order->id . ' berhasil diubah ke "' . ($order->affiliate->name ?? '-') . '".';
            if ($order->upline_id) {
                $message .= ' Upline "' . ($order->uplineUser->name ?? '-') . '" ikut di-set untuk bonus upline.';
            }
            if ($order->status === 'paid') {
                $message .= ' Komisi sudah dihitung ulang.';
            }
        } else {
            $message = 'Affiliator pesanan #' . $order->id . ' berhasil dihapus.'
                . ($order->status === 'paid' ? ' Komisi affiliator & upline sebelumnya sudah ditarik kembali.' : '');
        }

        return $this->redirectWithWarnings(back()->with('success', $message), $warnings);
    }

    /**
     * Saran 1-klik: tetapkan pemilik kupon sebagai affiliator. Dipakai untuk
     * mengoreksi pesanan lama yang memakai kupon upline tetapi affiliatornya
     * tidak terekam, sehingga komisi pemilik kupon (dan bonus upline-nya) bisa
     * dicairkan.
     */
    public function assignCouponOwner(Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        if (! $order->coupon_code) {
            return back()->with('error', 'Pesanan ini tidak memakai kupon.');
        }

        // Endpoint ini khusus untuk MENGOREKSI pesanan yang belum punya
        // affiliator. Bila sudah ada affiliator, jangan diam-diam membalik
        // komisinya — admin harus pakai menu "Ubah" (updateAffiliate) yang
        // eksplisit. Mencegah double-submit / tab basi / POST langsung yang
        // tidak sengaja mengganti atribusi yang sudah benar.
        if ($order->affiliate_id) {
            return back()->with('error', 'Pesanan ini sudah punya affiliator. Gunakan tombol "Ubah" untuk menggantinya.');
        }

        $coupon = Coupon::where('code', strtoupper($order->coupon_code))->with('members')->first();
        if (! $coupon) {
            return back()->with('error', 'Kupon "' . $order->coupon_code . '" tidak ditemukan, tidak bisa menentukan pemilik kupon.');
        }

        $owner = $this->resolveCouponOwner($coupon, $order);
        if (! $owner) {
            return back()->with('error', 'Tidak ada pemilik kupon yang cocok untuk dijadikan affiliator (kupon global atau hanya dimiliki pembeli).');
        }

        $product = $order->product;
        if ($product && $product->created_by && (int) $owner->id === (int) $product->created_by) {
            return back()->with('error', 'Pemilik kupon adalah pembuat produk, tidak bisa menjadi affiliator untuk produknya sendiri.');
        }

        $warnings = $paymentService->reassignAffiliate($order, (int) $owner->id);

        $order->refresh()->load(['affiliate', 'uplineUser']);

        $message = 'Affiliator pesanan #' . $order->id . ' di-set ke pemilik kupon "' . ($order->affiliate->name ?? '-') . '".';
        if ($order->upline_id) {
            $message .= ' Upline "' . ($order->uplineUser->name ?? '-') . '" mendapat bonus upline.';
        }
        if ($order->status === 'paid') {
            $message .= ' Komisi sudah dicairkan.';
        }

        return $this->redirectWithWarnings(back()->with('success', $message), $warnings);
    }

    /**
     * Bangun map [order_id => User pemilik kupon] untuk pesanan berkupon yang
     * belum punya affiliator. Di-batch agar tidak N+1.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Order>  $orders
     * @return array<int, \App\Models\User>
     */
    private function buildCouponOwnerSuggestions(Collection $orders): array
    {
        $eligible = $orders->filter(fn (Order $o) => $o->coupon_code && ! $o->affiliate_id);
        if ($eligible->isEmpty()) {
            return [];
        }

        $codes = $eligible->pluck('coupon_code')
            ->map(fn ($code) => strtoupper($code))
            ->unique()
            ->all();

        $coupons = Coupon::whereIn('code', $codes)
            ->with('members')
            ->get()
            ->keyBy(fn (Coupon $c) => strtoupper($c->code));

        $suggestions = [];
        foreach ($eligible as $order) {
            $coupon = $coupons->get(strtoupper($order->coupon_code));
            if (! $coupon) {
                continue;
            }

            $owner = $this->resolveCouponOwner($coupon, $order);

            // Jangan sarankan pemilik kupon yang ternyata pembuat produk.
            $product = $order->product;
            if ($owner && $product && $product->created_by && (int) $owner->id === (int) $product->created_by) {
                $owner = null;
            }

            if ($owner) {
                $suggestions[$order->id] = $owner;
            }
        }

        return $suggestions;
    }

    /**
     * Tentukan pemilik kupon yang layak dijadikan affiliator untuk order:
     * utamakan upline pembeli bila dia pemilik kupon, lain itu pemilik kupon
     * dengan id terkecil (deterministik). Pembeli sendiri selalu dikecualikan.
     *
     * Mengandalkan relasi members yang sudah di-eager-load.
     */
    private function resolveCouponOwner(Coupon $coupon, Order $order): ?User
    {
        $candidates = $coupon->members->filter(
            fn (User $m) => (int) $m->id !== (int) $order->user_id
        );

        if ($candidates->isEmpty()) {
            return null;
        }

        $buyer = $order->user;
        if ($buyer && $buyer->upline_id) {
            $upline = $candidates->firstWhere('id', $buyer->upline_id);
            if ($upline) {
                return $upline;
            }
        }

        return $candidates->sortBy('id')->first();
    }

    private function redirectWithWarnings(RedirectResponse $response, array $warnings): RedirectResponse
    {
        if (! empty($warnings)) {
            $response->with('warning', implode(' ', $warnings));
        }

        return $response;
    }
}
