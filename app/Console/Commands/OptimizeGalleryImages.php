<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientAsset;
use App\Support\ImageVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeGalleryImages extends Command
{
    protected $signature = 'gallery:optimize-images
                            {--force : Regenerate variants even when the cached file is newer}
                            {--client= : Only optimize one client id}';

    protected $description = 'Generate lightweight WebP thumbnails for locally stored client photos and covers';

    public function handle(): int
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $clientId = $this->option('client');

        if ($clientId !== null && ! ctype_digit((string) $clientId)) {
            $this->error('Opsi --client harus berupa id numerik.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $clientId = $clientId !== null ? (int) $clientId : null;

        $assets = ClientAsset::query()
            ->where('file_type', 'photo')
            ->whereNull('drive_file_id')
            ->whereNotNull('file_path')
            ->when($clientId !== null, fn ($query) => $query->where('client_id', $clientId))
            ->orderBy('id')
            ->get(['file_path']);

        $covers = Client::query()
            ->whereNull('cover_drive_file_id')
            ->whereNotNull('cover_image')
            ->when($clientId !== null, fn ($query) => $query->whereKey($clientId))
            ->pluck('cover_image');

        $tasks = collect();

        foreach ($assets as $asset) {
            $tasks->push([$asset->file_path, ImageVariant::THUMBNAIL_WIDTH]);
            $tasks->push([$asset->file_path, ImageVariant::FEATURE_WIDTH]);
        }

        foreach ($covers as $cover) {
            $tasks->push([$cover, ImageVariant::COVER_WIDTH]);
        }

        $tasks = $tasks
            ->unique(fn (array $task) => $task[0].'|'.$task[1])
            ->values();

        if ($tasks->isEmpty()) {
            $this->info('Tidak ada gambar lokal yang perlu dioptimalkan.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($tasks->count());
        $bar->start();

        $ready = 0;
        $missing = 0;
        $failed = 0;

        foreach ($tasks as [$sourcePath, $width]) {
            if (! Storage::disk('public')->exists($sourcePath)) {
                $missing++;
                $bar->advance();

                continue;
            }

            if (ImageVariant::generate($sourcePath, $width, $force) === null) {
                $failed++;
            } else {
                $ready++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("{$ready} thumbnail siap, {$missing} sumber tidak ditemukan, {$failed} gagal.");

        if ($failed > 0) {
            $this->warn('Jalankan ulang dengan --force untuk mencoba ulang file yang gagal.');
        }

        return self::SUCCESS;
    }
}
