<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $secret,
        TelegramService $telegram,
        OrderPaymentService $paymentService
    ): JsonResponse {
        $expectedSecret = Setting::get('telegram_webhook_secret');
        if (!$expectedSecret || !hash_equals($expectedSecret, $secret)) {
            return response()->json(['ok' => false, 'error' => 'invalid_secret'], 403);
        }

        $update = $request->json()->all();
        Log::info('Telegram webhook received', ['type' => array_keys($update)]);

        $callback = data_get($update, 'callback_query');
        if (!$callback) {
            return response()->json(['ok' => true]);
        }

        $data = data_get($callback, 'data');
        $callbackId = (string) data_get($callback, 'id');
        $message = data_get($callback, 'message');
        $messageId = (int) data_get($message, 'message_id');
        $hasPhoto = !empty(data_get($message, 'photo'));
        $originalText = $hasPhoto
            ? (string) data_get($message, 'caption', '')
            : (string) data_get($message, 'text', '');

        // Security: only respond if chat_id matches saved admin chat
        $expectedChatId = (string) Setting::get('telegram_chat_id');
        $actualChatId = (string) data_get($message, 'chat.id');
        if ($expectedChatId === '' || $actualChatId !== $expectedChatId) {
            $telegram->answerCallback($callbackId, '❌ Tidak diotorisasi');
            return response()->json(['ok' => true]);
        }

        [$action, $id] = array_pad(explode(':', (string) $data, 2), 2, null);
        $id = is_numeric($id) ? (int) $id : null;

        if (!$action || !$id) {
            $telegram->answerCallback($callbackId, 'Aksi tidak dikenali');
            return response()->json(['ok' => true]);
        }

        $resultMessage = '';
        $appendToOriginal = '';

        switch ($action) {
            case 'activate_member':
                $user = User::find($id);
                if (!$user) {
                    $telegram->answerCallback($callbackId, '❌ Member tidak ditemukan');
                    return response()->json(['ok' => true]);
                }
                if ($user->status === 'active') {
                    $telegram->answerCallback($callbackId, 'Member sudah aktif');
                    $appendToOriginal = "\n\n━━━━━━━━━━━━━\n<b>✅ Sudah Aktif</b>";
                } else {
                    $user->update(['status' => 'active']);
                    $resultMessage = '✅ Member ' . $user->name . ' diaktifkan';
                    $appendToOriginal = "\n\n━━━━━━━━━━━━━\n<b>✅ Diaktifkan via Telegram</b>";
                }
                break;

            case 'reject_member':
                $user = User::find($id);
                if (!$user) {
                    $telegram->answerCallback($callbackId, '❌ Member tidak ditemukan');
                    return response()->json(['ok' => true]);
                }
                $resultMessage = '❌ Member ' . $user->name . ' ditolak';
                $appendToOriginal = "\n\n━━━━━━━━━━━━━\n<b>❌ Ditolak via Telegram</b>\n<i>Member masih ada di sistem — hapus manual dari /admin/members jika perlu.</i>";
                break;

            case 'mark_paid':
                $order = Order::find($id);
                if (!$order) {
                    $telegram->answerCallback($callbackId, '❌ Pesanan tidak ditemukan');
                    return response()->json(['ok' => true]);
                }
                if ($order->status !== 'pending') {
                    $telegram->answerCallback($callbackId, 'Pesanan tidak pending (status: ' . $order->status . ')');
                    return response()->json(['ok' => true]);
                }
                if ($order->user && $order->user->status !== 'active') {
                    $order->user->update(['status' => 'active']);
                    $order->setRelation('user', $order->user->fresh());
                }
                $paymentService->markAsPaid($order);
                $resultMessage = '✅ Pesanan #' . $order->id . ' lunas';
                $appendToOriginal = "\n\n━━━━━━━━━━━━━\n<b>✅ Tandai Lunas via Telegram</b>\n<i>Komisi sudah diproses.</i>";
                break;

            case 'reject_order':
                $order = Order::find($id);
                if (!$order) {
                    $telegram->answerCallback($callbackId, '❌ Pesanan tidak ditemukan');
                    return response()->json(['ok' => true]);
                }
                if ($order->status !== 'pending') {
                    $telegram->answerCallback($callbackId, 'Pesanan tidak pending');
                    return response()->json(['ok' => true]);
                }
                $order->update(['status' => 'cancelled']);
                $resultMessage = '❌ Pesanan #' . $order->id . ' dibatalkan';
                $appendToOriginal = "\n\n━━━━━━━━━━━━━\n<b>❌ Dibatalkan via Telegram</b>";
                break;

            default:
                $telegram->answerCallback($callbackId, 'Aksi tidak dikenali');
                return response()->json(['ok' => true]);
        }

        $telegram->answerCallback($callbackId, $resultMessage ?: 'OK');

        $newText = $originalText . $appendToOriginal;
        if ($hasPhoto) {
            $telegram->editMessageCaption($messageId, $newText, []);
        } else {
            $telegram->editMessageText($messageId, $newText, []);
        }

        return response()->json(['ok' => true]);
    }
}
