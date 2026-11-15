<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeHomeImages extends Command
{
    protected $signature = 'home:optimize-images {--force : Regenerate even if the optimized file already exists}';

    protected $description = 'Generate responsive WebP variants for the home page images';

    private const WIDTHS = [480, 800, 1200, 1600];

    private const QUALITY = 78;

    public function handle(): int
    {
        $sourceDirectory = public_path('assets/Home');
        $targetDirectory = $sourceDirectory.'/opt';

        if (! is_dir($sourceDirectory)) {
            $this->error('Folder assets/Home tidak ditemukan.');

            return self::FAILURE;
        }

        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true)) {
            $this->error('Gagal membuat folder assets/Home/opt.');

            return self::FAILURE;
        }

        $files = array_merge(
            glob($sourceDirectory.'/*.jpg') ?: [],
            glob($sourceDirectory.'/*.jpeg') ?: [],
            glob($sourceDirectory.'/*.png') ?: [],
        );

        if ($files === []) {
            $this->warn('Tidak ada gambar di assets/Home.');

            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($files as $file) {
            $image = $this->createImage($file);

            if ($image === null) {
                $this->warn('Lewati '.basename($file).' (format tidak didukung).');

                continue;
            }

            $sourceWidth = imagesx($image);
            $sourceHeight = imagesy($image);
            $baseName = pathinfo($file, PATHINFO_FILENAME);

            foreach (self::WIDTHS as $width) {
                if ($width > $sourceWidth) {
                    continue;
                }

                $base = sprintf('%s/%s-%d', $targetDirectory, $baseName, $width);
                $height = max(1, (int) round($sourceHeight * $width / $sourceWidth));
                $resized = imagecreatetruecolor($width, $height);

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

                foreach (['' => $base.'.webp', '-gray' => $base.'-gray.webp', '-muted' => $base.'-muted.webp'] as $suffix => $target) {
                    if (! $this->option('force') && is_file($target) && filemtime($target) >= filemtime($file)) {
                        continue;
                    }

                    $variant = imagecreatetruecolor($width, $height);
                    imagecopy($variant, $resized, 0, 0, 0, 0, $width, $height);

                    if ($suffix === '-gray') {
                        imagefilter($variant, IMG_FILTER_GRAYSCALE);
                    } elseif ($suffix === '-muted') {
                        $gray = imagecreatetruecolor($width, $height);
                        imagecopy($gray, $resized, 0, 0, 0, 0, $width, $height);
                        imagefilter($gray, IMG_FILTER_GRAYSCALE);
                        imagecopymerge($variant, $gray, 0, 0, 0, 0, $width, $height, 28);
                        imagedestroy($gray);
                    }

                    imagewebp($variant, $target, self::QUALITY);
                    imagedestroy($variant);
                    $generated++;
                }

                imagedestroy($resized);
            }

            imagedestroy($image);
        }

        $this->info("Selesai. {$generated} file WebP dibuat di assets/Home/opt.");

        return self::SUCCESS;
    }

    private function createImage(string $file): ?\GdImage
    {
        if (! is_file($file)) {
            return null;
        }

        $image = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($file),
            'png' => @imagecreatefrompng($file),
            default => null,
        };

        return $image instanceof \GdImage ? $image : null;
    }
}
