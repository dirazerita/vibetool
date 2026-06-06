<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'telegramEnabled' => Setting::get('telegram_enabled') === '1',
            'telegramBotToken' => Setting::get('telegram_bot_token', ''),
            'telegramChatId' => Setting::get('telegram_chat_id', ''),
            'telegramWebhookUrl' => $this->resolveWebhookUrl(),
            'pakasirEnabled' => Setting::get('pakasir_enabled') === '1',
            'pakasirSlug' => Setting::get('pakasir_slug', ''),
            'pakasirApiKey' => Setting::get('pakasir_api_key', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $manualEnabled = $request->boolean('manual_payment_enabled');
        $telegramEnabled = $request->boolean('telegram_enabled');
        $pakasirEnabled = $request->boolean('pakasir_enabled');

        $request->validate(
            [
                'whatsapp_admin' => ['required', 'string', 'max:20', 'regex:/^(08|62|\+62)\d{6,15}$/'],
                'manual_payment_enabled' => ['nullable'],
                'manual_bank_name' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:100'],
                'manual_bank_account' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:100'],
                'manual_bank_holder' => [$manualEnabled ? 'required' : 'nullable', 'string', 'max:255'],
                'manual_payment_note' => ['nullable', 'string', 'max:2000'],
                'pakasir_enabled' => ['nullable'],
                'pakasir_slug' => [$pakasirEnabled ? 'required' : 'nullable', 'string', 'max:100'],
                'pakasir_api_key' => [$pakasirEnabled ? 'required' : 'nullable', 'string', 'max:255'],
                'telegram_enabled' => ['nullable'],
                'telegram_bot_token' => [$telegramEnabled ? 'required' : 'nullable', 'string', 'max:255'],
                'telegram_chat_id' => [$telegramEnabled ? 'required' : 'nullable', 'string', 'max:50'],
            ],
            [
                'whatsapp_admin.required' => 'Nomor WhatsApp admin wajib diisi.',
                'whatsapp_admin.regex' => 'Nomor WhatsApp harus berawalan 08, 62, atau +62.',
                'manual_bank_name.required' => 'Nama bank wajib diisi kalau pembayaran manual diaktifkan.',
                'manual_bank_account.required' => 'Nomor rekening wajib diisi kalau pembayaran manual diaktifkan.',
                'manual_bank_holder.required' => 'Atas nama wajib diisi kalau pembayaran manual diaktifkan.',
                'pakasir_slug.required' => 'Slug proyek wajib diisi kalau Pakasir diaktifkan.',
                'pakasir_api_key.required' => 'API Key wajib diisi kalau Pakasir diaktifkan.',
                'telegram_bot_token.required' => 'Bot Token wajib diisi kalau notifikasi Telegram diaktifkan.',
                'telegram_chat_id.required' => 'Chat ID wajib diisi kalau notifikasi Telegram diaktifkan.',
            ]
        );

        $normalized = PhoneNumber::normalize($request->input('whatsapp_admin'));

        Setting::set('whatsapp_admin', $normalized);
        Setting::set('manual_payment_enabled', $manualEnabled ? '1' : '0');
        Setting::set('manual_bank_name', $request->input('manual_bank_name'));
        Setting::set('manual_bank_account', $request->input('manual_bank_account'));
        Setting::set('manual_bank_holder', $request->input('manual_bank_holder'));
        Setting::set('manual_payment_note', $request->input('manual_payment_note'));

        Setting::set('pakasir_enabled', $pakasirEnabled ? '1' : '0');
        Setting::set('pakasir_slug', trim((string) $request->input('pakasir_slug')));
        Setting::set('pakasir_api_key', trim((string) $request->input('pakasir_api_key')));

        Setting::set('telegram_enabled', $telegramEnabled ? '1' : '0');
        Setting::set('telegram_bot_token', trim((string) $request->input('telegram_bot_token')));
        Setting::set('telegram_chat_id', trim((string) $request->input('telegram_chat_id')));

        if (empty(Setting::get('telegram_webhook_secret'))) {
            Setting::set('telegram_webhook_secret', Str::random(40));
        }

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function testTelegram(TelegramService $telegram): RedirectResponse
    {
        if (!$telegram->enabled()) {
            return redirect()->route('admin.settings')
                ->with('error', 'Notifikasi Telegram belum aktif. Aktifkan dan simpan token + chat ID terlebih dahulu.');
        }

        $me = $telegram->getMe();
        if (!data_get($me, 'ok')) {
            return redirect()->route('admin.settings')
                ->with('error', 'Bot tidak valid: ' . (data_get($me, 'description') ?: 'unknown error'));
        }

        $botUsername = data_get($me, 'result.username', '');
        $messageId = $telegram->sendMessage(
            "✅ <b>Test Notifikasi Telegram</b>\n\nBot <b>@{$botUsername}</b> terhubung dengan PRODIG. Notifikasi pesanan & member baru akan dikirim ke chat ini."
        );

        if ($messageId === null) {
            return redirect()->route('admin.settings')
                ->with('error', 'Gagal kirim test message. Pastikan Chat ID benar dan bot sudah pernah dichat dari akun tersebut.');
        }

        return redirect()->route('admin.settings')
            ->with('success', "Test message dikirim ke chat — cek Telegram untuk konfirmasi. Bot: @{$botUsername}.");
    }

    public function setupTelegramWebhook(TelegramService $telegram): RedirectResponse
    {
        if (!$telegram->enabled()) {
            return redirect()->route('admin.settings')
                ->with('error', 'Aktifkan & simpan token + chat ID dulu sebelum pasang webhook.');
        }

        $webhookUrl = $this->resolveWebhookUrl();
        if (!$webhookUrl) {
            return redirect()->route('admin.settings')
                ->with('error', 'Webhook secret belum ter-generate — simpan pengaturan dulu.');
        }

        if (!Str::startsWith($webhookUrl, 'https://')) {
            return redirect()->route('admin.settings')
                ->with('error', 'Telegram hanya menerima webhook HTTPS. App harus diakses via HTTPS (URL: ' . $webhookUrl . ').');
        }

        $result = $telegram->setWebhook($webhookUrl);
        if (!data_get($result, 'ok')) {
            return redirect()->route('admin.settings')
                ->with('error', 'Gagal pasang webhook: ' . (data_get($result, 'description') ?: 'unknown error'));
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Webhook berhasil dipasang ke Telegram. Tombol di pesan notifikasi sekarang akan aktif.');
    }

    private function resolveWebhookUrl(): ?string
    {
        $secret = Setting::get('telegram_webhook_secret');
        if (empty($secret)) {
            return null;
        }

        return url('/webhook/telegram/' . $secret);
    }
}
