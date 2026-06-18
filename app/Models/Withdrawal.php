<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'bank_name',
        'bank_account',
        'status',
        'note',
        'transfer_proof',
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

    public function hasTransferProof(): bool
    {
        return ! empty($this->transfer_proof);
    }

    public function transferProofUrl(): ?string
    {
        return $this->transfer_proof ? asset('storage/'.$this->transfer_proof) : null;
    }
}
