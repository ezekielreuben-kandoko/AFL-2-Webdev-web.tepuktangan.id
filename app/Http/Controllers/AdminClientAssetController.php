<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAsset;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Storage;

class AdminClientAssetController extends Controller
{
    public function sync(Client $client, GoogleDriveService $drive)
    {
        try {
            $count = $drive->syncToClient($client);

            return redirect()->route('admin.clients.assets', $client)
                ->with('success', $count.' aset disinkronkan dari Google Drive.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.clients.assets', $client)
                ->with('error', 'Gagal sync Google Drive: '.$e->getMessage());
        }
    }

    public function destroy(ClientAsset $clientAsset)
    {
        $clientId = $clientAsset->client_id;

        if ($clientAsset->file_path) {
            Storage::disk('public')->delete($clientAsset->file_path);
        }

        $clientAsset->delete();

        return redirect()->route('admin.clients.assets', $clientId)
            ->with('success', 'Aset dihapus dari website. File di Google Drive tidak dihapus.');
    }
}
