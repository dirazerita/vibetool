<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'product_id',
        'license_id',
        'event',
        'url',
        'payload',
        'signature',
        'status_code',
        'response_body',
        'error_message',
        'attempt',
        'result',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempt' => 'integer',
        'status_code' => 'integer',
        'delivered_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function isSuccess(): bool
    {
        return $this->result === 'success';
    }
}
