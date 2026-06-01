<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Illuminate\Support\Facades\Schema::getColumnListing('products');
$variants = Illuminate\Support\Facades\Schema::getColumnListing('product_variants');
echo "Products columns:\n";
print_r($products);
echo "\nVariants columns:\n";
print_r($variants);
