<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
$service = new App\Services\GoogleDriveService();

// Test folder 1: Stephen & Phoppy Wedding
echo "=== Stephen & Phoppy Wedding ===\n";
$result = $service->listMedia("1OIFAXLVo4k2WFUKM-sA5UMDOH6YcSY_8", "folder");
echo "Jumlah file: " . count($result) . "\n";
foreach ($result as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test folder 2: Erica & Alvin
echo "=== Erica & Alvin ===\n";
$result2 = $service->listMedia("1wOy3X01FI-ARh_u9Rui42lHpHoCbDT0K", "folder");
echo "Jumlah file: " . count($result2) . "\n";
foreach ($result2 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test folder 3: Anthony & Meilita Engagement (VIDEO)
echo "=== Anthony & Meilita Engagement ===\n";
$result3 = $service->listMedia("1r7y5q0g2u6b2zgIbydlDtTxB3r58AK6R", "folder");
echo "Jumlah file: " . count($result3) . "\n";
foreach ($result3 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test folder 4: Anthony & Meilita Wedding
echo "=== Anthony & Meilita Wedding ===\n";
$result4 = $service->listMedia("18MFW5fldva2TZWqtEfkS1gT2ug8xWYnd", "folder");
echo "Jumlah file: " . count($result4) . "\n";
foreach ($result4 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test folder 5: Andy & Lydia Wedding
echo "=== Andy & Lydia Wedding ===\n";
$result5 = $service->listMedia("1HX0BndrCEFJbkUJBf8D6Fd4rKzsd6E60", "folder");
echo "Jumlah file: " . count($result5) . "\n";
foreach ($result5 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test photobooth folder
echo "=== Photobooth (bts live cam) ===\n";
$result6 = $service->listMedia("1NaHtNYVpRso5ApcCQyV-zn0gwjBZKOt5", "folder");
echo "Jumlah file: " . count($result6) . "\n";
foreach ($result6 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

// Test livestream folder
echo "=== Livestream ===\n";
$result7 = $service->listMedia("1L64Rq2padJAQMvbppKNmqqrzkfCbOWxA", "folder");
echo "Jumlah file: " . count($result7) . "\n";
foreach ($result7 as $f) {
    echo "  - " . $f['name'] . " (id: " . $f['id'] . ", mime: " . $f['mimeType'] . ")\n";
}
echo "\n";

echo "=== SELESAI ===\n";