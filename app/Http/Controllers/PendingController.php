<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use App\Models\Product;
use Illuminate\Http\Request;

class PendingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $data = session('pending_user_data');

        if ($user) {
            $name = $user->name;
            $email = $user->email;
            $whatsappNumber = $user->whatsapp_number;
        } elseif (is_array($data)) {
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $whatsappNumber = $data['whatsapp_number'] ?? null;
        } else {
            return redirect()->route('home');
        }

        // Resolve produk yang ingin dibeli (kalau ada) — prioritas session pending_user_data,
        // lalu intended_product_slug, supaya admin bisa lihat produk apa yang user mau beli.
        $product = null;
        if (is_array($data) && !empty($data['product_title'])) {
            $product = [
                'title' => $data['product_title'],
                'slug' => $data['product_slug'] ?? null,
                'price' => $data['product_price'] ?? null,
            ];
        } else {
            $intendedSlug = session('intended_product_slug');
            if ($intendedSlug) {
                $resolvedProduct = Product::where('slug', $intendedSlug)->first();
                if ($resolvedProduct) {
                    $product = [
                        'title' => $resolvedProduct->title,
                        'slug' => $resolvedProduct->slug,
                        'price' => (float) $resolvedProduct->price,
                    ];
                }
            }
        }

        $activationLink = WhatsApp::activationLink($name, $email, $whatsappNumber, $product);

        return view('auth.pending', [
            'name' => $name,
            'email' => $email,
            'whatsappNumber' => $whatsappNumber,
            'product' => $product,
            'activationLink' => $activationLink,
        ]);
    }
}
