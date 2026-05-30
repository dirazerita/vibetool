<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'label',
        'duration_type',
        'price',
        'compare_at_price',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Pretty label that always falls back to duration if admin left it blank.
     */
    public function displayLabel(): string
    {
        if (! empty($this->label)) {
            return $this->label;
        }

        return match ($this->duration_type) {
            '1_month' => '1 Bulan',
            '6_months' => '6 Bulan',
            '1_year' => '1 Tahun',
            'lifetime' => 'Lifetime',
            default => ucfirst((string) $this->duration_type),
        };
    }
}
