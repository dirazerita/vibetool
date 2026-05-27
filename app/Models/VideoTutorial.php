<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoTutorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'video_url',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Extract YouTube embed URL from various YouTube URL formats.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        $url = $this->video_url;
        if (!$url) {
            return null;
        }

        // YouTube formats
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Already an embed URL
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        return $url;
    }
}
