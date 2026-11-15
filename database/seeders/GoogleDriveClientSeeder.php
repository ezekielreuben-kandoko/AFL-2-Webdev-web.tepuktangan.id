<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Services\GoogleDriveService;
use Illuminate\Database\Seeder;

class GoogleDriveClientSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, type: string, url: string, folder_id: string, month_date: string|null}>
     */
    private const CLIENTS = [
        [
            'name' => 'Andy & Lydia',
            'type' => 'gallery',
            'month_date' => null,
            'url' => 'https://drive.google.com/drive/folders/1yAi-7Y2wYHuuWrfZ8O0qNlXbVbomEWc4?usp=share_link',
            'folder_id' => '1yAi-7Y2wYHuuWrfZ8O0qNlXbVbomEWc4',
        ],
        [
            'name' => 'Aset Galerie',
            'type' => 'gallery',
            'month_date' => null,
            'url' => 'https://drive.google.com/drive/folders/1TXdXLy6SCbYxToJzOVqawrciSti5NNJU?usp=sharing',
            'folder_id' => '1TXdXLy6SCbYxToJzOVqawrciSti5NNJU',
        ],
    ];

    public function run(): void
    {
        $drive = app(GoogleDriveService::class);

        foreach (self::CLIENTS as $data) {
            $client = Client::query()->updateOrCreate(
                ['client_name' => $data['name']],
                [
                    'client_type' => $data['type'],
                    'month_date' => $data['month_date'],
                    'drive_folder_id' => $data['folder_id'],
                    'drive_folder_url' => $data['url'],
                    'drive_link_type' => 'folder',
                ]
            );

            $client->driveSources()->updateOrCreate(
                ['drive_id' => $data['folder_id']],
                [
                    'url' => $data['url'],
                    'link_type' => 'folder',
                    'sort_order' => 0,
                ]
            );

            try {
                $count = $drive->syncToClient($client);
                $this->command?->info("Synced {$count} aset untuk {$data['name']}.");
            } catch (\Throwable $e) {
                $this->command?->warn("Gagal sync {$data['name']}: ".$e->getMessage());
            }
        }
    }
}
