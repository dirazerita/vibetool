<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WebhookDelivery;
use App\Services\WebhookDispatcher;

class WebhookDeliveryController extends Controller
{
    public function index(Product $product)
    {
        abort_unless($product->isSoftware(), 404);

        $deliveries = $product->webhookDeliveries()
            ->with(['license:id,key,product_id'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.webhook-deliveries.index', compact('product', 'deliveries'));
    }

    public function show(Product $product, WebhookDelivery $delivery)
    {
        abort_unless($product->isSoftware(), 404);
        abort_unless((int) $delivery->product_id === (int) $product->id, 404);

        $delivery->load(['license:id,key,product_id']);

        return view('admin.webhook-deliveries.show', compact('product', 'delivery'));
    }

    public function retry(Product $product, WebhookDelivery $delivery, WebhookDispatcher $dispatcher)
    {
        abort_unless($product->isSoftware(), 404);
        abort_unless((int) $delivery->product_id === (int) $product->id, 404);

        $result = $dispatcher->retry($delivery);

        $msg = $result->isSuccess()
            ? 'Webhook berhasil dikirim ulang (HTTP '.$result->status_code.').'
            : 'Webhook gagal dikirim ulang: '.($result->error_message ?? ('HTTP '.$result->status_code));

        return redirect()
            ->route('admin.products.webhook-deliveries', $product)
            ->with($result->isSuccess() ? 'success' : 'error', $msg);
    }
}
