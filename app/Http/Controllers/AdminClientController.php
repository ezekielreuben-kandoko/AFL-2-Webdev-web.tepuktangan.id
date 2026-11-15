<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request, GoogleDriveService $drive)
    {
        $validated = $this->validateClient($request);
        $links = $this->parseLinks($validated['drive_links'] ?? '', $drive);

        $client = Client::create([
            'client_name' => $validated['client_name'],
            'client_type' => $validated['client_type'],
            'month_date' => $validated['month_date'] ?? null,
            'cover_drive_file_id' => $this->coverFileId($validated, $drive),
        ]);

        $hasSources = $this->saveDriveSources($client, $links);

        $message = 'Klien berhasil ditambahkan.';

        if ($hasSources) {
            $syncMessage = $this->syncDrive($client, $drive);

            if ($syncMessage) {
                $message .= ' '.$syncMessage;
            }
        }

        return redirect()->route('admin.clients.index')
            ->with('success', $message);
    }

    public function edit(Client $client)
    {
        $client->load('driveSources');

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client, GoogleDriveService $drive)
    {
        $validated = $this->validateClient($request);
        $links = $this->parseLinks($validated['drive_links'] ?? '', $drive);

        $previousLinks = $client->driveSources()->orderBy('sort_order')->pluck('url')->implode("\n");

        $client->update([
            'client_name' => $validated['client_name'],
            'client_type' => $validated['client_type'],
            'month_date' => $validated['month_date'] ?? null,
        ]);

        if (! empty($validated['cover_drive_url'])) {
            $client->update(['cover_drive_file_id' => $this->coverFileId($validated, $drive)]);
        }

        $hasSources = $this->saveDriveSources($client, $links);

        $currentLinks = $client->driveSources()->orderBy('sort_order')->pluck('url')->implode("\n");
        $sourcesChanged = $previousLinks !== $currentLinks;

        $message = 'Klien berhasil diperbarui.';

        if ($hasSources && ($sourcesChanged || ! $client->drive_synced_at)) {
            $syncMessage = $this->syncDrive($client, $drive);

            if ($syncMessage) {
                $message .= ' '.$syncMessage;
            }
        }

        return redirect()->route('admin.clients.index')
            ->with('success', $message);
    }

    public function destroy(Client $client)
    {
        if ($client->cover_image) {
            Storage::disk('public')->delete($client->cover_image);
        }

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Klien berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateClient(Request $request): array
    {
        return $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'client_type' => ['required', 'string', 'max:50'],
            'month_date' => ['nullable', 'string', 'max:255'],
            'drive_links' => ['nullable', 'string'],
            'cover_drive_url' => ['nullable', 'string', 'max:2048'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function coverFileId(array $validated, GoogleDriveService $drive): ?string
    {
        if (empty($validated['cover_drive_url'])) {
            return null;
        }

        $cover = $drive->parseLink($validated['cover_drive_url']);

        if (! $cover['id']) {
            throw ValidationException::withMessages([
                'cover_drive_url' => 'Link cover Google Drive tidak valid.',
            ]);
        }

        return $cover['id'];
    }

    /**
     * @return array<int, array{url: string, id: string, type: string}>
     */
    private function parseLinks(string $links, GoogleDriveService $drive): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $links))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->map(function (string $line) use ($drive) {
                $link = $drive->parseLink($line);

                if (! $link['id']) {
                    throw ValidationException::withMessages([
                        'drive_links' => 'Link Google Drive tidak valid: '.$line,
                    ]);
                }

                return ['url' => $line, 'id' => $link['id'], 'type' => $link['type']];
            })
            ->all();
    }

    /**
     * Replace the client's Drive sources with the parsed links.
     *
     * @param  array<int, array{url: string, id: string, type: string}>  $links
     */
    private function saveDriveSources(Client $client, array $links): bool
    {
        $existing = $client->driveSources()->get()->keyBy('drive_id');
        $keptIds = [];

        foreach ($links as $index => $link) {
            $source = $existing->get($link['id']);

            if ($source) {
                $source->update([
                    'url' => $link['url'],
                    'link_type' => $link['type'],
                    'sort_order' => $index,
                ]);
            } else {
                $source = $client->driveSources()->create([
                    'url' => $link['url'],
                    'drive_id' => $link['id'],
                    'link_type' => $link['type'],
                    'sort_order' => $index,
                ]);
            }

            $keptIds[] = $source->id;
        }

        $removedIds = $client->driveSources()
            ->whereNotIn('id', $keptIds ?: [0])
            ->pluck('id');

        if ($removedIds->isNotEmpty()) {
            $client->assets()->whereIn('client_drive_source_id', $removedIds)->delete();
            $client->driveSources()->whereIn('id', $removedIds)->delete();
        }

        return $keptIds !== [];
    }

    private function syncDrive(Client $client, GoogleDriveService $drive): ?string
    {
        try {
            $count = $drive->syncToClient($client);

            return $count.' aset disinkronkan dari Google Drive.';
        } catch (\Throwable $e) {
            report($e);

            session()->flash('warning', 'Sync Google Drive gagal: '.$e->getMessage());

            return null;
        }
    }
}
