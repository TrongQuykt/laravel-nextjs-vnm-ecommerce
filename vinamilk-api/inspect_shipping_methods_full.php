<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$methods = \App\Models\ShippingMethod::all();
foreach ($methods as $m) {
    echo json_encode($m->toArray(), JSON_PRETTY_PRINT) . "\n";
}
