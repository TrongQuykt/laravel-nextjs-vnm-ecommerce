<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$v1 = \App\Models\ProductVariant::with('product')->find(154);
$v2 = \App\Models\ProductVariant::with('product')->find(163);

echo "V154 variant image: " . $v1->main_image . "\n";
echo "V154 product image: " . $v1->product->main_image . "\n";
echo "V163 variant image: " . $v2->main_image . "\n";
echo "V163 product image: " . $v2->product->main_image . "\n";
