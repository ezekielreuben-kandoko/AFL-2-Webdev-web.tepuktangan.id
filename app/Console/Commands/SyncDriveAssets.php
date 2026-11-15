<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class SyncDriveAssets extends Command
{
    protected $signature = 'drive:sync {client? : ID klien tertentu}';

    protected $description = 'Sinkronkan aset klien dari link Google Drive';

    public function handle(GoogleDriveService $drive): int
    {
        $clients = $this->argument('client')
            ? Client::query()->whereKey($this->argument('client'))->get()
            : Client::query()->whereNotNull('drive_folder_id')->get();

        if ($clients->isEmpty()) {
            $this->warn('Tidak ada klien dengan link Google Drive.');

            return self::SUCCESS;
        }

        foreach ($clients as $client) {
            try {
                $count = $drive->syncToClient($client);
                $this->info("{$client->client_name}: {$count} aset disinkronkan.");
            } catch (\Throwable $e) {
                report($e);
                $this->error("{$client->client_name}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
