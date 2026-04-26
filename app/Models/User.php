<?php

namespace App\Models;

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
        'bank_name',
        'bank_account',
        'balance',
        'role',
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id');
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
}
