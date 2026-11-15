<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use Illuminate\Support\Facades\Storage;

echo "=== DEBUG IMAGE LOADING ===\n\n";

// Check a few photobooth clients
$clients = Client::where('client_type', 'photobooth')->limit(5)->get();

foreach ($clients as $client) {
    echo "Client: {$client->client_name}\n";
    echo "  cover_image: " . ($client->cover_image ?? 'NULL') . "\n";
    echo "  cover_drive_file_id: " . ($client->cover_drive_file_id ?? 'NULL') . "\n";
    
    // Compute cover_url the same way the accessor does
    $coverUrl = $client->cover_url;
    echo "  cover_url: $coverUrl\n";
    
    // Check if file exists
    $exists = false;
    if ($client->cover_image) {
        $exists = Storage::disk('public')->exists($client->cover_image);
        echo "  storage exists ({$client->cover_image}): " . ($exists ? 'YES' : 'NO') . "\n";
    }
    if (!$exists && $client->cover_drive_file_id) {
        $drivePath = 'drive/' . $client->cover_drive_file_id . '-w800.jpg';
        $exists = Storage::disk('public')->exists($drivePath);
        echo "  drive cache exists ({$drivePath}): " . ($exists ? 'YES' : 'NO') . "\n";
    }
    
    // What would the browser see as src?
    echo "  -> img src would be: {$coverUrl}\n";
    echo "\n";
}

// Also check gallery clients
echo "=== Gallery clients ===\n";
$galleryClients = Client::where('client_type', 'gallery')->limit(3)->get();
foreach ($galleryClients as $client) {
    echo "Client: {$client->client_name}\n";
    echo "  cover_image: " . ($client->cover_image ?? 'NULL') . "\n";
    echo "  cover_url: " . ($client->cover_url ?? 'NULL') . "\n";
    if ($client->cover_image) {
        $exists = Storage::disk('public')->exists($client->cover_image);
        echo "  storage exists: " . ($exists ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
}

echo "=== DONE ===\n";