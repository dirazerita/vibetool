<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PromoTemplateMedia extends Model
{
    const TYPE_IMAGE = 'image';

    const TYPE_VIDEO = 'video';

    protected $table = 'promo_template_media';

    protected $fillable = [
        'promo_template_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'sort_order',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PromoTemplate::class, 'promo_template_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size_bytes;
        if ($bytes <= 0) {
            return '';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $bytes < 10 && $i > 0 ? 1 : 0, ',', '.').' '.$units[$i];
    }
}
