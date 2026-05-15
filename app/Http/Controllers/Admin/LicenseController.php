<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    public function index()
    {
        $products = Product::where('product_type', 'software')
            ->withCount([
                'licenses as total_licenses',
                'licenses as assigned_licenses' => function ($q) {
                    $q->whereNotNull('order_id');
                },
                'licenses as available_licenses' => function ($q) {
                    $q->whereNull('order_id');
                },
                'orders as paid_orders' => function ($q) {
                    $q->where('status', 'paid');
                },
            ])
            ->orderBy('title')
            ->paginate(20);

        return view('admin.licenses.index', compact('products'));
    }

    public function show(Product $product)
    {
        abort_unless($product->isSoftware(), 404);

        $licenses = $product->licenses()
            ->with(['user', 'order'])
            ->orderByRaw('order_id IS NULL DESC')
            ->orderBy('assigned_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30);

        $pendingOrders = Order::where('product_id', $product->id)
            ->where('status', 'paid')
            ->whereDoesntHave('license')
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $availableCount = $product->licenses()->whereNull('order_id')->count();

        return view('admin.licenses.show', compact('product', 'licenses', 'pendingOrders', 'availableCount'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->isSoftware(), 404);

        $request->validate([
            'keys' => 'required|string',
            'extra_info' => 'nullable|string',
        ]);

        $rawKeys = preg_split('/\r\n|\r|\n/', $request->input('keys'));
        $keys = array_values(array_unique(array_filter(array_map('trim', $rawKeys))));

        if (empty($keys)) {
            return back()->withErrors(['keys' => 'Tidak ada kunci lisensi yang valid.'])->withInput();
        }

        $existing = License::where('product_id', $product->id)
            ->whereIn('key', $keys)
            ->pluck('key')
            ->all();
        $existingSet = array_flip($existing);

        $created = 0;
        $skipped = count($existing);
        $extraInfo = $request->input('extra_info') ?: null;

        DB::transaction(function () use ($product, $keys, $existingSet, $extraInfo, &$created) {
            foreach ($keys as $key) {
                if (isset($existingSet[$key])) {
                    continue;
                }
                License::create([
                    'product_id' => $product->id,
                    'key' => $key,
                    'extra_info' => $extraInfo,
                ]);
                $created++;
            }
        });

        $message = "Berhasil menambah {$created} lisensi.";
        if ($skipped > 0) {
            $message .= " {$skipped} lisensi dilewati karena duplikat.";
        }

        return back()->with('success', $message);
    }

    public function destroy(License $license)
    {
        if ($license->isAssigned()) {
            return back()->withErrors(['license' => 'Lisensi yang sudah dialokasikan ke member tidak bisa dihapus.']);
        }

        $productId = $license->product_id;
        $license->delete();

        return redirect()->route('admin.licenses.show', $productId)->with('success', 'Lisensi dihapus.');
    }

    public function assignOrder(Order $order, OrderPaymentService $service)
    {
        if ($order->status !== 'paid') {
            return back()->withErrors(['order' => 'Order belum dibayar.']);
        }

        if ($order->license) {
            return back()->withErrors(['order' => 'Order sudah memiliki lisensi.']);
        }

        $product = $order->product;
        if (!$product || !$product->isSoftware()) {
            return back()->withErrors(['order' => 'Produk bukan software.']);
        }

        DB::transaction(function () use ($order) {
            $license = License::where('product_id', $order->product_id)
                ->whereNull('order_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($license) {
                $license->update([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'assigned_at' => now(),
                ]);
            }
        });

        if (!$order->fresh()->license) {
            return back()->withErrors(['order' => 'Stok lisensi habis. Tambah lisensi baru dulu.']);
        }

        return back()->with('success', 'Lisensi berhasil dialokasikan ke order #' . $order->id . '.');
    }
}
