<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function enabled(): bool
    {
        return Setting::get('telegram_enabled') === '1'
            && !empty(Setting::get('telegram_bot_token'))
            && !empty(Setting::get('telegram_chat_id'));
    }

    private function apiUrl(string $method): string
    {
        $token = Setting::get('telegram_bot_token');

        return "https://api.telegram.org/bot{$token}/{$method}";
    }

    public function sendMessage(string $text, array $inlineButtons = []): ?int
    {
        if (!$this->enabled()) {
            return null;
        }

        $payload = [
            'chat_id' => Setting::get('telegram_chat_id'),
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if (!empty($inlineButtons)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineButtons]);
        }

        try {
            $response = Http::asJson()->timeout(15)->post($this->apiUrl('sendMessage'), $payload);
            $json = $response->json();
            if ($response->successful() && data_get($json, 'ok')) {
                return (int) data_get($json, 'result.message_id');
            }
            Log::warning('Telegram sendMessage failed', ['response' => $json]);
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function sendPhoto(string $photoPath, string $caption, array $inlineButtons = []): ?int
    {
        if (!$this->enabled()) {
            return null;
        }

        $payload = [
            'chat_id' => Setting::get('telegram_chat_id'),
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ];

        if (!empty($inlineButtons)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineButtons]);
        }

        try {
            $response = Http::timeout(60)
                ->attach('photo', file_get_contents($photoPath), basename($photoPath))
                ->post($this->apiUrl('sendPhoto'), $payload);
            $json = $response->json();
            if ($response->successful() && data_get($json, 'ok')) {
                return (int) data_get($json, 'result.message_id');
            }
            Log::warning('Telegram sendPhoto failed', ['response' => $json]);
        } catch (\Throwable $e) {
            Log::error('Telegram sendPhoto exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function editMessageText(int $messageId, string $newText, array $inlineButtons = []): bool
    {
        $payload = [
            'chat_id' => Setting::get('telegram_chat_id'),
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineButtons]);

        try {
            $response = Http::asJson()->timeout(15)->post($this->apiUrl('editMessageText'), $payload);

            return $response->successful() && (bool) data_get($response->json(), 'ok');
        } catch (\Throwable $e) {
            Log::error('Telegram editMessageText exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function editMessageCaption(int $messageId, string $newCaption, array $inlineButtons = []): bool
    {
        $payload = [
            'chat_id' => Setting::get('telegram_chat_id'),
            'message_id' => $messageId,
            'caption' => $newCaption,
            'parse_mode' => 'HTML',
        ];
        $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineButtons]);

        try {
            $response = Http::asJson()->timeout(15)->post($this->apiUrl('editMessageCaption'), $payload);

            return $response->successful() && (bool) data_get($response->json(), 'ok');
        } catch (\Throwable $e) {
            Log::error('Telegram editMessageCaption exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function answerCallback(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        $payload = ['callback_query_id' => $callbackQueryId];
        if ($text !== null) {
            $payload['text'] = mb_substr($text, 0, 200);
            $payload['show_alert'] = $showAlert;
        }

        try {
            Http::asJson()->timeout(5)->post($this->apiUrl('answerCallbackQuery'), $payload);

            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram answerCallback exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function setWebhook(string $url): array
    {
        try {
            $response = Http::asJson()->timeout(15)->post($this->apiUrl('setWebhook'), [
                'url' => $url,
                'allowed_updates' => ['callback_query'],
            ]);

            return $response->json() ?? ['ok' => false, 'description' => 'no response'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    public function getMe(): array
    {
        try {
            $response = Http::asJson()->timeout(15)->get($this->apiUrl('getMe'));

            return $response->json() ?? ['ok' => false, 'description' => 'no response'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    // ===== Domain-specific notifications =====

    public function notifyNewMember(User $user): void
    {
        if (!$this->enabled()) {
            return;
        }

        $user->loadMissing(['upline', 'intendedProduct']);

        $lines = [
            '<b>📋 Member Baru Mendaftar</b>',
            '',
            '<b>Nama:</b> ' . e($user->name),
            '<b>Email:</b> ' . e($user->email),
            '<b>WhatsApp:</b> ' . e($user->whatsapp_number ?? '-'),
        ];

        if ($user->upline) {
            $lines[] = '<b>Upline / Afiliator:</b> ' . e($user->upline->name);
        }

        if ($user->intendedProduct) {
            $lines[] = '<b>Ingin Beli:</b> ' . e($user->intendedProduct->title)
                . ' (Rp ' . number_format($user->intendedProduct->price, 0, ',', '.') . ')';
        }

        $lines[] = '';
        $lines[] = '⏳ <i>Menunggu aktivasi admin</i>';

        $buttons = [[
            ['text' => '✅ Aktifkan Member', 'callback_data' => 'activate_member:' . $user->id],
            ['text' => '❌ Tolak', 'callback_data' => 'reject_member:' . $user->id],
        ]];

        $this->sendMessage(implode("\n", $lines), $buttons);
    }

    public function notifyNewOrder(Order $order): void
    {
        if (!$this->enabled()) {
            return;
        }

        $order->loadMissing(['user', 'product', 'affiliate']);

        $lines = [
            '<b>🛒 Pesanan Baru #' . $order->id . '</b>',
            '',
            '<b>Produk:</b> ' . e($order->product->title ?? '-'),
            '<b>Pembeli:</b> ' . e($order->user->name ?? '-'),
            '<b>Email:</b> ' . e($order->user->email ?? '-'),
            '<b>WhatsApp:</b> ' . e($order->user->whatsapp_number ?? '-'),
        ];
        if ($order->affiliate) {
            $lines[] = '<b>Afiliator:</b> ' . e($order->affiliate->name);
        }
        if ($order->coupon_code) {
            $lines[] = '<b>Kupon:</b> ' . e($order->coupon_code)
                . ' (-Rp ' . number_format((float) $order->discount_amount, 0, ',', '.') . ')';
        }
        $lines[] = '<b>Total:</b> Rp ' . number_format((float) $order->amount, 0, ',', '.');
        $lines[] = '<b>Metode:</b> ' . ($order->payment_method === 'manual' ? 'Transfer Bank Manual' : 'Xendit');
        $lines[] = '';
        $lines[] = '⏳ <i>Menunggu pembayaran</i>';

        $buttons = [];
        if ($order->payment_method === 'manual') {
            $buttons[] = [
                ['text' => '✅ Tandai Lunas', 'callback_data' => 'mark_paid:' . $order->id],
                ['text' => '❌ Tolak', 'callback_data' => 'reject_order:' . $order->id],
            ];
        }

        $this->sendMessage(implode("\n", $lines), $buttons);
    }

    public function notifyPaymentProof(Order $order): void
    {
        if (!$this->enabled()) {
            return;
        }

        $order->loadMissing(['user', 'product', 'affiliate']);

        $lines = [
            '<b>📸 Bukti Transfer Diupload</b>',
            '',
            '<b>Order:</b> #' . $order->id,
            '<b>Produk:</b> ' . e($order->product->title ?? '-'),
            '<b>Pembeli:</b> ' . e($order->user->name ?? '-'),
            '<b>Email:</b> ' . e($order->user->email ?? '-'),
            '<b>WhatsApp:</b> ' . e($order->user->whatsapp_number ?? '-'),
        ];
        if ($order->affiliate) {
            $lines[] = '<b>Afiliator:</b> ' . e($order->affiliate->name);
        }
        if ($order->coupon_code) {
            $lines[] = '<b>Kupon:</b> ' . e($order->coupon_code);
        }
        $lines[] = '<b>Total:</b> Rp ' . number_format((float) $order->amount, 0, ',', '.');

        $buttons = [[
            ['text' => '✅ Tandai Lunas', 'callback_data' => 'mark_paid:' . $order->id],
            ['text' => '❌ Tolak', 'callback_data' => 'reject_order:' . $order->id],
        ]];

        $proofPath = $order->payment_proof
            ? storage_path('app/public/' . $order->payment_proof)
            : null;

        if ($proofPath && is_file($proofPath)) {
            $this->sendPhoto($proofPath, implode("\n", $lines), $buttons);
        } else {
            $lines[] = '';
            $lines[] = '⚠️ <i>File bukti tidak ditemukan di server</i>';
            $this->sendMessage(implode("\n", $lines), $buttons);
        }
    }
}
