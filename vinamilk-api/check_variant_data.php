<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check a stock movement record
$movement = \App\Models\StockMovement::with(['productVariant.volume', 'productVariant.packagingType'])->first();

if ($movement) {
    echo "Stock Movement ID: " . $movement->id . "\n";
    echo "Product Variant ID: " . $movement->product_variant_id . "\n";
    
    $variant = $movement->productVariant;
    if ($variant) {
        echo "Variant Name: " . $variant->name . "\n";
        echo "Volume ID: " . ($variant->volume_id ?? 'null') . "\n";
        echo "Packaging Type ID: " . ($variant->packaging_type_id ?? 'null') . "\n";
        
        if ($variant->volume) {
            echo "Volume Name: " . $variant->volume->name . "\n";
        } else {
            echo "Volume: null\n";
        }
        
        if ($variant->packagingType) {
            echo "Packaging Type Name: " . $variant->packagingType->name . "\n";
        } else {
            echo "Packaging Type: null\n";
        }
        
        // Test the format logic
        $volume = $variant->volume?->name ?? '';
        $packaging = $variant->packagingType?->name ?? '';
        $parts = array_filter([$volume, $packaging]);
        $result = !empty($parts) ? implode(' - ', $parts) : $variant->name;
        echo "Formatted Result: " . $result . "\n";
    } else {
        echo "Variant: null\n";
    }
} else {
    echo "No stock movements found\n";
}
