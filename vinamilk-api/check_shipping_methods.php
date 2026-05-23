<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$methods = \App\Models\ShippingMethod::all();
foreach ($methods as $m) {
    echo "ID: {$m->id} | Provider: {$m->provider} | Name: '{$m->name}' | Active: " . ($m->is_active ? 'Yes' : 'No') . "\n";
}
