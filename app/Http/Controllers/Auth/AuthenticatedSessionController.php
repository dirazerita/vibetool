<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && !$user->isAdmin() && ($user->status ?? 'active') === 'pending') {
            $pendingData = [
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp_number' => $user->whatsapp_number,
            ];

            $intendedSlug = session('intended_product_slug');
            if ($intendedSlug) {
                $product = Product::where('slug', $intendedSlug)->first();
                if ($product) {
                    $pendingData['product_title'] = $product->title;
                    $pendingData['product_slug'] = $product->slug;
                    $pendingData['product_price'] = (float) $product->price;
                }
            }

            session(['pending_user_data' => $pendingData]);

            return redirect()->route('pending');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
