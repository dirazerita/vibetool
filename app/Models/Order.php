<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'affiliate_id',
        'upline_id',
        'amount',
        'coupon_code',
        'discount_amount',
        'status',
        'xendit_id',
        'download_token',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function uplineUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
