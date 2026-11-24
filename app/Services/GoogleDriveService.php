<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveService
{
    private const FILES_ENDPOINT = 'https://www.googleapis.com/drive/v3/files';

    private const FOLDER_MIME = 'application/vnd.google-apps.folder';

    /**
     * Parse a Google Drive link (folder, file, or raw ID) into its ID and type.
     *
     * @return array{id: ?string, type: ?string}
     */
    public function parseLink(string $url): array
    {
        $url = trim($url);

        if ($url === '') {
            return ['id' => null, 'type' => null];
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return ['id' => $matches[1], 'type' => 'folder'];
        }

        if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return ['id' => $matches[1], 'type' => 'file'];
        }

        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return ['id' => $matches[1], 'type' => 'file'];
        }

        if (preg_match('#^[a-zA-Z0-9_-]{20,}$#', $url)) {
            return ['id' => $url, 'type' => 'folder'];
        }

        return ['id' => null, 'type' => null];
    }

    /**
     * List every image/video inside a Drive folder or return a single file.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listMedia(string $id, string $type = 'folder'): array
    {
        if ($type === 'file') {
            $file = $this->getFile($id);

            return $file ? [$file] : [];
        }

        return $this->listFolder($id, []);
    }

    /**
     * Sync every Drive source of a client into the client_assets table.
     */
    public function syncToClient(Client $client): int
    {
        $this->ensureLegacySource($client);

        $sources = $client->driveSources()->get();

        if ($sources->isEmpty()) {
            throw new RuntimeException('Belum ada link Google Drive untuk klien ini.');
        }

        $syncedIds = [];

        foreach ($sources as $source) {
            $files = collect($this->listMedia($source->drive_id, $source->link_type ?: 'folder'))
                ->unique('id')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            $keptIds = [];

            foreach ($files as $index => $file) {
                $mimeType = (string) ($file['mimeType'] ?? '');

                $client->assets()->updateOrCreate(
                    ['drive_file_id' => $file['id']],
                    [
                        'client_drive_source_id' => $source->id,
                        'file_path' => null,
                        'file_type' => str_starts_with($mimeType, 'video/') ? 'video' : 'photo',
                        'source' => 'gdrive',
                        'mime_type' => $mimeType,
                        'alt' => $file['name'] ?? $client->client_name,
                        'sort_order' => $index,
                    ]
                );

                $keptIds[] = $file['id'];
                $syncedIds[] = $file['id'];
            }

            $stale = $client->assets()->where('client_drive_source_id', $source->id);

            if ($keptIds !== []) {
                $stale->whereNotIn('drive_file_id', $keptIds);
            }

            $stale->delete();

            $source->forceFill(['last_synced_at' => now()])->save();
        }

        $orphans = $client->assets()->where('source', 'gdrive')->whereNull('client_drive_source_id');

        if ($syncedIds !== []) {
            $orphans->whereNotIn('drive_file_id', $syncedIds);
        }

        $orphans->delete();

        if (! $client->cover_drive_file_id) {
            $firstPhoto = $client->assets()
                ->where('file_type', 'photo')
                ->whereNotNull('drive_file_id')
                ->orderBy('sort_order')
                ->first();

            $coverAsset = $firstPhoto ?: $client->assets()
                ->whereNotNull('drive_file_id')
                ->orderBy('sort_order')
                ->first();

            if ($coverAsset) {
                $client->cover_drive_file_id = $coverAsset->drive_file_id;
            }
        }

        $client->drive_synced_at = now();
        $client->save();

        return count(array_unique($syncedIds));
    }

    private function ensureLegacySource(Client $client): void
    {
        if ($client->driveSources()->exists() || ! $client->drive_folder_id) {
            return;
        }

        $client->driveSources()->create([
            'url' => $client->drive_folder_url ?: $client->drive_folder_id,
            'drive_id' => $client->drive_folder_id,
            'link_type' => $client->drive_link_type ?: 'folder',
            'sort_order' => 0,
        ]);
    }

    /**
     * @param  array<int, string>  $visited
     * @return array<int, array<string, mixed>>
     */
    private function listFolder(string $folderId, array $visited): array
    {
        if (in_array($folderId, $visited, true)) {
            return [];
        }

        $visited[] = $folderId;
        $files = [];
        $pageToken = null;

        do {
            $response = $this->request([
                'q' => "'{$folderId}' in parents and trashed = false",
                'fields' => 'nextPageToken, files(id, name, mimeType, size, modifiedTime)',
                'pageSize' => 1000,
                'pageToken' => $pageToken,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

            $payload = $response->json();

            foreach ($payload['files'] ?? [] as $file) {
                $mimeType = (string) ($file['mimeType'] ?? '');

                if ($mimeType === self::FOLDER_MIME) {
                    $files = array_merge($files, $this->listFolder($file['id'], $visited));

                    continue;
                }

                if ($this->isMedia($mimeType)) {
                    $files[] = $file;
                }
            }

            $pageToken = $payload['nextPageToken'] ?? null;
        } while ($pageToken);

        return $files;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getFile(string $fileId): ?array
    {
        $response = $this->request(
            ['fields' => 'id, name, mimeType, size, modifiedTime', 'supportsAllDrives' => 'true'],
            $fileId
        );

        $file = $response->json();

        if (! is_array($file) || ! $this->isMedia((string) ($file['mimeType'] ?? ''))) {
            return null;
        }

        return $file;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function request(array $query, ?string $fileId = null): Response
    {
        $apiKey = config('services.google.drive_api_key');

        if (! $apiKey) {
            throw new RuntimeException('GOOGLE_DRIVE_API_KEY belum diset di .env.');
        }

        $url = $fileId ? self::FILES_ENDPOINT.'/'.$fileId : self::FILES_ENDPOINT;

        $response = Http::timeout(60)
            ->get($url, array_filter($query, fn ($value) => $value !== null && $value !== '') + ['key' => $apiKey]);

        if ($response->failed()) {
            $message = $response->json('error.message') ?: $response->body();

            throw new RuntimeException('Google Drive API error: '.$message);
        }

        return $response;
    }

    private function isMedia(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/') || str_starts_with($mimeType, 'video/');
    }
}
