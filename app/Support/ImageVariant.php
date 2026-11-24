<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageVariant
{
    public const THUMBNAIL_WIDTH = 600;

    public const FEATURE_WIDTH = 1200;

    public const COVER_WIDTH = 800;

    private const QUALITY = 78;

    public static function path(string $sourcePath, int $width): string
    {
        return sprintf('thumbnails/%d/%s.webp', $width, sha1($sourcePath));
    }

    public static function generate(string $sourcePath, int $width, bool $force = false): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath) || ! function_exists('imagewebp')) {
            return null;
        }

        $sourceFile = $disk->path($sourcePath);
        $targetPath = self::path($sourcePath, $width);
        $targetFile = $disk->path($targetPath);

        if (! $force && is_file($targetFile) && filemtime($targetFile) >= filemtime($sourceFile)) {
            return $targetPath;
        }

        $image = self::createImage($sourceFile);

        if ($image === null) {
            return null;
        }

        $image = self::applyOrientation($image, $sourceFile);
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            return null;
        }

        $targetWidth = min($width, $sourceWidth);
        $targetHeight = max(1, (int) round($sourceHeight * $targetWidth / $sourceWidth));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagealphablending($resized, true);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $disk->makeDirectory(dirname($targetPath));

        $saved = imagewebp($resized, $targetFile, self::QUALITY);

        if (! $saved) {
            @unlink($targetFile);

            return null;
        }

        return $targetPath;
    }

    private static function createImage(string $file): ?\GdImage
    {
        if (! is_file($file)) {
            return null;
        }

        $image = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($file),
            'png' => @imagecreatefrompng($file),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : null,
            default => null,
        };

        return $image instanceof \GdImage ? $image : null;
    }

    private static function applyOrientation(\GdImage $image, string $file): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = @imagerotate($image, $angle, 0);

        if ($rotated === false) {
            return $image;
        }

        return $rotated;
    }
}
