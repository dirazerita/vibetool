<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    public const UPDATED_AT = null;

    public const SCOPE_ALL = 'all';

    public const SCOPE_ACTIVE = 'active';

    protected $fillable = [
        'admin_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'audience_scope',
        'recipients_count',
        'sent_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'sent_at' => 'datetime',
        'attachment_size' => 'integer',
        'recipients_count' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'broadcast_id');
    }

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }

    public function isImage(): bool
    {
        return $this->hasAttachment() && str_starts_with((string) $this->attachment_mime, 'image/');
    }

    public function attachmentSizeHuman(): string
    {
        if (! $this->attachment_size) {
            return '';
        }
        $bytes = (int) $this->attachment_size;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }

    public function audienceLabel(): string
    {
        return match ($this->audience_scope) {
            self::SCOPE_ACTIVE => 'Member aktif',
            self::SCOPE_ALL => 'Semua member',
            default => $this->audience_scope,
        };
    }
}
