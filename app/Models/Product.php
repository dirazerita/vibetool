<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'commission_percent',
        'upline_percent',
        'file_path',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'upline_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_products')->withPivot('created_at');
    }

    public function landingPage(): HasOne
    {
        return $this->hasOne(ProductLandingPage::class);
    }

    public function landingPageImages(): HasMany
    {
        return $this->hasMany(LandingPageImage::class)->orderBy('sort_order');
    }

    public function landingPageTestimonials(): HasMany
    {
        return $this->hasMany(LandingPageTestimonial::class)->orderBy('sort_order');
    }
}
