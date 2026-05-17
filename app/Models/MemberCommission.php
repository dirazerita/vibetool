<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'commission_percent',
        'upline_percent',
    ];

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'upline_percent' => 'decimal:2',
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
}
