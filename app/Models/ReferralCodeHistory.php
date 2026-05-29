<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCodeHistory extends Model
{
    public const UPDATED_AT = null;

    public const ROLE_SELF = 'self';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SYSTEM = 'system';

    protected $fillable = [
        'user_id',
        'old_code',
        'new_code',
        'changed_by_id',
        'changed_by_role',
        'ip_address',
        'user_agent',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
