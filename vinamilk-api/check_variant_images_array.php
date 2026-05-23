<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$v1 = \App\Models\ProductVariant::with('product')->find(154);
$v2 = \App\Models\ProductVariant::with('product')->find(163);

echo "V154 variant images: " . json_encode($v1->images) . "\n";
echo "V163 variant images: " . json_encode($v2->images) . "\n";
