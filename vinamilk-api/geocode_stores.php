<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;

$stores = Store::whereNull('latitude')->get();
echo "Found " . $stores->count() . " stores to geocode.\n";

foreach ($stores as $store) {
    echo "Processing: " . $store->name . " (Address: " . $store->address . ")...\n";
    $store->save(); 
    if ($store->latitude) {
        echo "✅ SUCCESS: Lat: " . $store->latitude . ", Lon: " . $store->longitude . "\n";
    } else {
        echo "❌ FAILED\n";
    }
    sleep(2); // Increased sleep to avoid rate limiting
}

echo "Finished.\n";
