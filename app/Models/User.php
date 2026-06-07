<?php

namespace App\Models;

use App\Helpers\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'whatsapp_number',
        'password',
        'referral_code',
        'upline_id',
        'intended_product_id',
        'profile_photo',
        'bank_name',
        'bank_account',
        'social_instagram',
        'social_facebook',
        'social_twitter',
        'social_tiktok',
        'social_youtube',
        'social_website',
        'balance',
        'role',
        'status',
        'can_upload_product',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'can_upload_product' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
            }
        });
    }

    public function setWhatsappNumberAttribute($value): void
    {
        $this->attributes['whatsapp_number'] = PhoneNumber::normalize(is_string($value) ? $value : (string) $value);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    public function intendedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'intended_product_id');
    }

    public function downlines(): HasMany
    {
        return $this->hasMany(User::class, 'upline_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function affiliateOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'affiliate_id');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_members')->withPivot('created_at');
    }

    public function canUploadProduct(): bool
    {
        return (bool) $this->can_upload_product;
    }

    public function createdProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function referralCodeHistories(): HasMany
    {
        return $this->hasMany(ReferralCodeHistory::class)->latest('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    public function unreadAdminMessagesCount(): int
    {
        return $this->messages()
            ->where('sender_role', Message::ROLE_ADMIN)
            ->whereNull('read_at')
            ->count();
    }
}
