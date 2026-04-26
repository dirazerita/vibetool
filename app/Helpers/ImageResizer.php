<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageResizer
{
    private static function manager(): ImageManager
    {
        return new ImageManager(new Driver());
    }

    /**
     * Resize & crop hero image to 1200x630 (16:9), save as JPEG 85%.
     */
    public static function resizeHero(UploadedFile $file, string $directory = 'landing-pages'): string
    {
        $image = self::manager()->read($file->getPathname());
        $image->cover(1200, 630);

        $filename = uniqid('hero_') . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $image->toJpeg(85)->toString());

        return $path;
    }

    /**
     * Resize gallery image to max 800x600, keep aspect ratio, save as JPEG 85%.
     */
    public static function resizeGallery(UploadedFile $file, string $directory = 'landing-pages/gallery'): string
    {
        $image = self::manager()->read($file->getPathname());
        $image->scaleDown(800, 600);

        $filename = uniqid('gallery_') . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $image->toJpeg(85)->toString());

        return $path;
    }

    /**
     * Resize & crop avatar to 200x200 (square), save as JPEG 85%.
     */
    public static function resizeAvatar(UploadedFile $file, string $directory = 'landing-pages/avatars'): string
    {
        $image = self::manager()->read($file->getPathname());
        $image->cover(200, 200);

        $filename = uniqid('avatar_') . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $image->toJpeg(85)->toString());

        return $path;
    }
}
