<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_IN_DEVELOPMENT = 'in_development';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING => 'Baru Masuk',
        self::STATUS_REVIEWING => 'Sedang Direview',
        self::STATUS_ACCEPTED => 'Diterima',
        self::STATUS_DECLINED => 'Ditolak',
        self::STATUS_IN_DEVELOPMENT => 'Sedang Dikerjakan',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    public const STATUS_COLORS = [
        self::STATUS_PENDING => '#64748b',
        self::STATUS_REVIEWING => '#3b82f6',
        self::STATUS_ACCEPTED => '#10b981',
        self::STATUS_DECLINED => '#ef4444',
        self::STATUS_IN_DEVELOPMENT => '#f59e0b',
        self::STATUS_COMPLETED => '#8b5cf6',
    ];

    public const PLATFORMS = [
        'android' => 'Android',
        'ios' => 'iOS',
        'web' => 'Web Browser',
        'windows' => 'Windows Desktop',
        'mac' => 'Mac Desktop',
        'unknown' => 'Belum Tahu / Terserah',
    ];

    public const URGENCIES = [
        'flexible' => 'Tidak buru-buru',
        '1-3-months' => 'Dalam 1-3 bulan',
        '1-month' => 'Dalam 1 bulan',
        'asap' => 'Secepatnya',
    ];

    public const BUDGETS = [
        'unknown' => 'Belum tahu',
        'under-1m' => 'Di bawah Rp 1.000.000',
        '1m-5m' => 'Rp 1.000.000 – Rp 5.000.000',
        '5m-20m' => 'Rp 5.000.000 – Rp 20.000.000',
        'above-20m' => 'Di atas Rp 20.000.000',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'purpose',
        'target_users',
        'problem_to_solve',
        'similar_apps',
        'platforms',
        'key_features',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'budget_range',
        'urgency',
        'additional_notes',
        'status',
        'admin_notes',
        'admin_response',
        'product_id',
        'admin_responded_at',
        'user_seen_response_at',
    ];

    protected $casts = [
        'platforms' => 'array',
        'attachment_size' => 'integer',
        'admin_responded_at' => 'datetime',
        'user_seen_response_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#64748b';
    }

    public function platformLabels(): array
    {
        $platforms = $this->platforms ?? [];

        return array_map(fn ($p) => self::PLATFORMS[$p] ?? $p, $platforms);
    }

    public function urgencyLabel(): ?string
    {
        return $this->urgency ? (self::URGENCIES[$this->urgency] ?? $this->urgency) : null;
    }

    public function budgetLabel(): ?string
    {
        return $this->budget_range ? (self::BUDGETS[$this->budget_range] ?? $this->budget_range) : null;
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

    public function hasUnseenResponse(): bool
    {
        if (! $this->admin_responded_at) {
            return false;
        }
        if (! $this->user_seen_response_at) {
            return true;
        }

        return $this->admin_responded_at->gt($this->user_seen_response_at);
    }
}
