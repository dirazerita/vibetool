<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings', [
            'whatsappAdmin' => Setting::get('whatsapp_admin', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'whatsapp_admin' => ['required', 'string', 'max:20', 'regex:/^(08|62|\+62)\d{6,15}$/'],
            ],
            [
                'whatsapp_admin.required' => 'Nomor WhatsApp admin wajib diisi.',
                'whatsapp_admin.regex' => 'Nomor WhatsApp harus berawalan 08, 62, atau +62.',
            ]
        );

        $normalized = PhoneNumber::normalize($request->input('whatsapp_admin'));

        Setting::set('whatsapp_admin', $normalized);

        return redirect()->route('admin.settings')->with('success', 'Nomor WhatsApp admin berhasil disimpan.');
    }
}
