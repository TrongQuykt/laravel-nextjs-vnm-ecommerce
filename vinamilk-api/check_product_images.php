<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$v1 = \App\Models\ProductVariant::with(['product.images', 'product.volumeMedia'])->find(154);

echo "Product ID: " . $v1->product->id . "\n";
echo "Product main_image: " . $v1->product->main_image . "\n";
echo "Product images: " . json_encode($v1->product->images) . "\n";
echo "Product volume media: " . json_encode($v1->product->volumeMedia) . "\n";
