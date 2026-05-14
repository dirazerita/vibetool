<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download(string $token)
    {
        $order = Order::where('download_token', $token)
            ->where('status', 'paid')
            ->firstOrFail();

        $product = $order->product;

        if ($product && $product->file_url) {
            return redirect()->away($product->file_url);
        }

        $filePath = $product->file_path ?? null;

        if ($filePath && Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, $product->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'File tidak ditemukan.');
    }
}
