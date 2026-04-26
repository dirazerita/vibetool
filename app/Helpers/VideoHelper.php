<?php

namespace App\Helpers;

class VideoHelper
{
    public static function getEmbedUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return $url;
    }
}
