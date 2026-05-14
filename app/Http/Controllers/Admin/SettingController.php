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
            'manualPaymentEnabled' => Setting::get('manual_payment_enabled') === '1',
            'manualBankName' => Setting::get('manual_bank_name', ''),
            'manualBankAccount' => Setting::get('manual_bank_account', ''),
            'manualBankHolder' => Setting::get('manual_bank_holder', ''),
            'manualPaymentNote' => Setting::get('manual_payment_note', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $manualEnabled = $request->boolean('manual_payment_enabled');

        $request->validate(
            [
                'whatsapp_admin' => ['required', 'string', 'max:20', 'regex:/^(08|62|\+62)\d{6,15}$/'],
                'manual_payment_enabled' => ['nullable'],
                'manual_bank_name' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:100'],
                'manual_bank_account' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:100'],
                'manual_bank_holder' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:255'],
                'manual_payment_note' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'whatsapp_admin.required' => 'Nomor WhatsApp admin wajib diisi.',
                'whatsapp_admin.regex' => 'Nomor WhatsApp harus berawalan 08, 62, atau +62.',
                'manual_bank_name.required' => 'Nama bank wajib diisi kalau pembayaran manual diaktifkan.',
                'manual_bank_account.required' => 'Nomor rekening wajib diisi kalau pembayaran manual diaktifkan.',
                'manual_bank_holder.required' => 'Atas nama wajib diisi kalau pembayaran manual diaktifkan.',
            ]
        );

        $normalized = PhoneNumber::normalize($request->input('whatsapp_admin'));

        Setting::set('whatsapp_admin', $normalized);
        Setting::set('manual_payment_enabled', $manualEnabled ? '1' : '0');
        Setting::set('manual_bank_name', $request->input('manual_bank_name'));
        Setting::set('manual_bank_account', $request->input('manual_bank_account'));
        Setting::set('manual_bank_holder', $request->input('manual_bank_holder'));
        Setting::set('manual_payment_note', $request->input('manual_payment_note'));

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
