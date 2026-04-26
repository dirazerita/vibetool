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

        $filePath = $order->product->file_path;

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, $order->product->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
        }

        return back()->with('error', 'File tidak ditemukan.');
    }
}
