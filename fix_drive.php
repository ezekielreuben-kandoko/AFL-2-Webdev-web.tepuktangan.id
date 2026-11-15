<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = $app["db"];

echo "=== Membersihkan cover_drive_file_id untuk photobooth ===\n";
$updated = $db->table("clients")
    ->whereNotNull("cover_drive_file_id")
    ->where("client_type", "photobooth")
    ->update(["cover_drive_file_id" => null]);
echo "Berhasil update: $updated client\n";

$remaining = $db->table("clients")->whereNotNull("cover_drive_file_id")->count();
echo "Sisa client dengan cover_drive_file_id: $remaining\n";

echo "\n=== Checking all types ===\n";
$types = $db->table("clients")->distinct()->pluck("client_type");
foreach ($types as $type) {
    $count = $db->table("clients")->where("client_type", $type)->count();
    $hasDrive = $db->table("clients")->where("client_type", $type)->whereNotNull("cover_drive_file_id")->count();
    echo "$type: $count clients, $hasDrive punya cover_drive_file_id\n";
}

echo "\n=== Done ===\n";