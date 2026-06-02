<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test the accessor
$movement = \App\Models\StockMovement::with(['productVariant.volume', 'productVariant.packagingType'])->first();

if ($movement) {
    echo "Stock Movement ID: " . $movement->id . "\n";
    echo "Variant Display Name (accessor): " . $movement->variant_display_name . "\n";
    
    $variant = $movement->productVariant;
    if ($variant) {
        echo "Variant Name: " . $variant->name . "\n";
        echo "Volume: " . ($variant->volume?->name ?? 'null') . "\n";
        echo "Packaging: " . ($variant->packagingType?->name ?? 'null') . "\n";
    }
} else {
    echo "No stock movements found\n";
}
