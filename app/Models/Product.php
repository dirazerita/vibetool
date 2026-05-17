<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Models\MemberCommission;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'commission_percent',
        'commission_percent_non_owner',
        'upline_percent',
        'upline_percent_non_owner',
        'product_type',
        'license_duration',
        'file_path',
        'file_url',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_percent_non_owner' => 'decimal:2',
            'upline_percent' => 'decimal:2',
            'upline_percent_non_owner' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Apakah user yang diberikan sudah pernah membeli (paid) produk ini.
     */
    public function isOwnedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return Order::where('user_id', $user->id)
            ->where('product_id', $this->id)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Tarif komisi direct (%) yang berlaku untuk user.
     * Cek komisi khusus dulu, lalu fallback ke tarif produk.
     */
    public function commissionPercentFor(?User $user): float
    {
        if ($user) {
            $custom = MemberCommission::where('user_id', $user->id)
                ->where('product_id', $this->id)
                ->first();
            if ($custom && $custom->commission_percent !== null) {
                return (float) $custom->commission_percent;
            }
        }

        if ($this->isOwnedBy($user)) {
            return (float) $this->commission_percent;
        }

        return (float) ($this->commission_percent_non_owner ?? $this->commission_percent);
    }

    /**
     * Tarif bonus upline (%) yang berlaku untuk user.
     * Cek komisi khusus dulu, lalu fallback ke tarif produk.
     */
    public function uplinePercentFor(?User $user): float
    {
        if ($user) {
            $custom = MemberCommission::where('user_id', $user->id)
                ->where('product_id', $this->id)
                ->first();
            if ($custom && $custom->upline_percent !== null) {
                return (float) $custom->upline_percent;
            }
        }

        if ($this->isOwnedBy($user)) {
            return (float) $this->upline_percent;
        }

        return (float) ($this->upline_percent_non_owner ?? $this->upline_percent);
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

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function isSoftware(): bool
    {
        return $this->product_type === 'software';
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
