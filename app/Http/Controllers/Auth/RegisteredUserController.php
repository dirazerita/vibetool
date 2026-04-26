<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $ref = $this->resolveRefCode($request);

        $refMemberName = null;
        if ($ref) {
            $refMember = User::where('referral_code', $ref)->first();
            if ($refMember) {
                $refMemberName = $refMember->name;
                session(['ref_code' => $ref]);
            }
        }

        return view('auth.register', compact('ref', 'refMemberName'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedWhatsapp = PhoneNumber::normalize($request->input('whatsapp_number'));
        $request->merge(['whatsapp_number' => $normalizedWhatsapp]);

        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'whatsapp_number' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('users', 'whatsapp_number'),
                ],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor yang berbeda.',
            ]
        );

        $uplineId = null;
        $refCode = $this->resolveRefCode($request);
        if ($refCode) {
            $upline = User::where('referral_code', $refCode)->first();
            if ($upline) {
                $uplineId = $upline->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $normalizedWhatsapp,
            'password' => Hash::make($request->password),
            'upline_id' => $uplineId,
            'status' => 'pending',
        ]);

        Log::info('DEBUG register stored', [
            'user_id' => $user->id,
            'upline_id' => $user->upline_id,
            'ref_code_used' => $refCode,
            'session_auto_coupon' => session('auto_coupon'),
            'session_ref_code' => session('ref_code'),
            'session_auto_coupon_member_id' => session('auto_coupon_member_id'),
        ]);

        event(new Registered($user));

        // Sistem aktivasi via WhatsApp: akun pending, tidak otomatis login.
        // Simpan data user untuk halaman pending lalu logout & redirect.
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

        Auth::guard('web')->logout();

        return redirect()->route('pending');
    }

    private function resolveRefCode(Request $request): ?string
    {
        $candidates = [
            $request->input('ref'),
            $request->cookie('ref'),
            session('ref_code'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }
}
