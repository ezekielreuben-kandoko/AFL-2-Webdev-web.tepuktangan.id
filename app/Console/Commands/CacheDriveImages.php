<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientAsset;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CacheDriveImages extends Command
{
    protected $signature = 'drive:cache-images {--force : Unduh ulang gambar yang sudah tersimpan}';

    protected $description = 'Simpan thumbnail Google Drive ke storage lokal agar halaman galeri tidak lambat.';

    private const THUMBNAIL_WIDTH = 600;

    private const FEATURE_WIDTH = 1200;

    private const COVER_WIDTH = 800;

    private const CONCURRENCY = 20;

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $photos = ClientAsset::query()
            ->where('file_type', 'photo')
            ->whereNotNull('drive_file_id')
            ->get(['drive_file_id']);

        $clients = Client::query()
            ->with(['photos' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->get();

        $tasks = [];

        foreach ($photos as $photo) {
            $this->addTask($tasks, $photo->drive_file_id, self::THUMBNAIL_WIDTH);
        }

        foreach ($clients as $client) {
            $this->addTask($tasks, $client->photos->first()?->drive_file_id, self::FEATURE_WIDTH);
            $this->addTask($tasks, $client->cover_drive_file_id, self::COVER_WIDTH);
        }

        $tasks = array_values(array_filter(
            $tasks,
            fn (array $task) => $force || ! Storage::disk('public')->exists($task['path'])
        ));

        if ($tasks === []) {
            $this->info('Semua gambar Google Drive sudah tersimpan di storage lokal.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($tasks));
        $bar->start();

        $cached = 0;
        $failed = 0;

        foreach (array_chunk($tasks, self::CONCURRENCY) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk) {
                foreach ($chunk as $index => $task) {
                    $pool->as((string) $index)
                        ->timeout(30)
                        ->retry(2, 200)
                        ->get($task['url']);
                }
            });

            foreach ($chunk as $index => $task) {
                $response = $responses[$index] ?? null;

                if ($response instanceof Response
                    && $response->successful()
                    && str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                    Storage::disk('public')->put($task['path'], $response->body());
                    $cached++;
                } else {
                    $failed++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("{$cached} gambar disimpan, {$failed} gagal.");

        if ($failed > 0) {
            $this->warn('Jalankan ulang perintah ini untuk mencoba gambar yang gagal.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, array{id: string, width: int, path: string, url: string}>  $tasks
     */
    private function addTask(array &$tasks, ?string $driveId, int $width): void
    {
        if (! $driveId) {
            return;
        }

        $path = 'drive/'.$driveId.'-w'.$width.'.jpg';

        if (isset($tasks[$path])) {
            return;
        }

        $tasks[$path] = [
            'id' => $driveId,
            'width' => $width,
            'path' => $path,
            'url' => 'https://lh3.googleusercontent.com/d/'.$driveId.'=w'.$width,
        ];
    }
}
