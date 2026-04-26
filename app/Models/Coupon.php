<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_uses',
        'used_count',
        'expired_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'expired_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_members')->withPivot('created_at');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products')->withPivot('created_at');
    }

    public function isValidForUser(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expired_at && $this->expired_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->members()->count() > 0 && !$this->members()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }

    public function isValidForProduct(Product $product): bool
    {
        if ($this->products()->count() > 0 && !$this->products()->where('product_id', $product->id)->exists()) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $price): float
    {
        if ($price < $this->min_purchase) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            return round($price * $this->discount_value / 100, 2);
        }

        return min($this->discount_value, $price);
    }
}
