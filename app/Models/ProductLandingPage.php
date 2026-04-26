<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'video_url',
        'about_content',
        'is_published',
        'hero_title_font',
        'hero_title_size',
        'hero_title_color',
        'hero_subtitle_font',
        'hero_subtitle_color',
        'about_font',
        'about_color',
        'about_bg_color',
        'testimonial_title_color',
        'testimonial_bg_color',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
