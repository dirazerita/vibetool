<?php

namespace App\Helpers;

use App\Models\Setting;

class WhatsApp
{
    /**
     * Membuat URL wa.me untuk meminta admin mengaktifkan akun member.
     * Mengembalikan null jika nomor admin tidak terkonfigurasi.
     *
     * @param  array<string, mixed>|null  $product  ['title' => ..., 'slug' => ..., 'price' => ...]
     */
    public static function activationLink(string $name, string $email, ?string $whatsappNumber, ?array $product = null): ?string
    {
        $rawAdmin = Setting::get('whatsapp_admin');
        $adminNumber = PhoneNumber::normalize($rawAdmin);

        if (!$adminNumber) {
            return null;
        }

        $message = "Halo Admin PRODIG \xF0\x9F\x91\x8B\nSaya ingin mengaktifkan membership saya.\n\n"
            . "Nama: {$name}\n"
            . "Email: {$email}\n"
            . 'No WA: ' . ($whatsappNumber ?: '-') . "\n";

        if ($product && !empty($product['title'])) {
            $message .= "\nProduk yang ingin dibeli: " . $product['title'] . "\n";
            if (isset($product['price']) && is_numeric($product['price'])) {
                $message .= 'Harga: Rp ' . number_format((float) $product['price'], 0, ',', '.') . "\n";
            }
        }

        $message .= "\nMohon akun saya diaktifkan. Terima kasih!";

        return 'https://wa.me/' . $adminNumber . '?text=' . rawurlencode($message);
    }

    public static function adminNumber(): ?string
    {
        return PhoneNumber::normalize(Setting::get('whatsapp_admin'));
    }
}
