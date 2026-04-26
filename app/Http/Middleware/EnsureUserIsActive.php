<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isAdmin() && ($user->status ?? 'active') === 'pending') {
            $pendingData = [
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
            ];

            // Coba ambil produk dari URL /checkout/{slug} kalau user pending mencoba checkout,
            // jatuh kembali ke session intended_product_slug.
            $slug = $request->route('slug') ?? session('intended_product_slug');
            if ($slug) {
                $product = Product::where('slug', $slug)->first();
                if ($product) {
                    $pendingData['product_title'] = $product->title;
                    $pendingData['product_slug'] = $product->slug;
                    $pendingData['product_price'] = (float) $product->price;
                }
            }

            session(['pending_user_data' => $pendingData]);

            return redirect()->route('pending')->with('warning', 'Akun Anda masih menunggu aktivasi oleh admin.');
        }

        return $next($request);
    }
}
