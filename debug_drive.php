<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = $app["db"];

echo "=== SEMUA CLIENT DAN FIELD COVER ===\n";
$clients = $db->table("clients")->get();
foreach ($clients as $c) {
    $type = $c->client_type;
    $name = $c->client_name;
    $ci = $c->cover_image;
    $cfid = $c->cover_drive_file_id;
    $covered = !is_null($ci) || !is_null($cfid);
    echo str_pad("$type / $name", 35) . " | cover_image: " . str_pad($ci ? substr($ci, 0, 35) : "NULL", 38) . " | cover_drive_file_id: " . str_pad($cfid ? substr($cfid, 0, 18) : "NULL", 20) . " | " . ($covered ? "TERSEDIA" : "TIDAK TERSEDIA") . "\n";
}

echo "\n=== CEK FILE DRIVE CACHE ===\n";
$driveClients = $clients->whereNotNull("cover_drive_file_id");
foreach ($driveClients as $c) {
    $path = "drive/" . $c->cover_drive_file_id . "-w800.jpg";
    $exists = Storage::disk("public")->exists($path);
    $status = $exists ? "EXISTS" : "MISSING";
    echo "Client {$c->client_name}: $path -> $status\n";
}

echo "\n=== CEK FILE COVER IMAGE ===\n";
$imageClients = $clients->whereNotNull("cover_image");
foreach ($imageClients as $c) {
    $exists = Storage::disk("public")->exists($c->cover_image);
    $status = $exists ? "EXISTS" : "MISSING";
    echo "Client {$c->client_name}: {$c->cover_image} -> $status\n";
}

echo "\n=== SEEDER COVER_IMAGE YANG KOSONG ===\n";
$empty = $clients->whereNull("cover_image")->count();
echo "Jumlah client dengan cover_image NULL: $empty\n";

echo "\n=== SEEDER COVER_DRIVE_FILE_ID YANG KOSONG ===\n";
$empty2 = $clients->whereNull("cover_drive_file_id")->count();
echo "Jumlah client dengan cover_drive_file_id NULL: $empty2\n";