<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use Illuminate\Support\Facades\Storage;

echo "=== CHECK COVER_URL FOR CLIENTS ===\n\n";

$clients = Client::where('client_type', 'photobooth')->limit(10)->get();

foreach ($clients as $client) {
    echo "Client: {$client->client_name}\n";
    echo "  ID: {$client->id}\n";
    echo "  cover_image: " . ($client->cover_image ?? 'NULL') . "\n";
    echo "  cover_drive_file_id: " . ($client->cover_drive_file_id ?? 'NULL') . "\n";
    echo "  cover_url: " . ($client->cover_url ?? 'NULL') . "\n";
    
    // Check if storage file exists
    if ($client->cover_image) {
        $storagePath = 'public/' . $client->cover_image;
        echo "  storage exists: " . (Storage::disk('public')->exists($client->cover_image) ? 'YES' : 'NO') . "\n";
    }
    
    // Check drive cached file
    if ($client->cover_drive_file_id) {
        $drivePath = 'drive/' . $client->cover_drive_file_id . '-w800.jpg';
        echo "  drive cache exists: " . (Storage::disk('public')->exists($drivePath) ? 'YES' : 'NO') . "\n";
    }
    
    echo "\n";
}

echo "\n=== CHECK ALL CLIENT TYPES ===\n";
$types = Client::distinct()->pluck('client_type');
foreach ($types as $type) {
    $count = Client::where('client_type', $type)->count();
    echo "$type: $count clients\n";
}

echo "\nDone.\n";