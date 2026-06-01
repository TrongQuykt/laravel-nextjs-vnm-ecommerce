<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::where('order_number', 'ES-260519105432HP8F')->first();
if (!$order) {
    echo "Order not found\n";
    exit;
}

// Find the incorrect gift item (which got variant_id = 26)
$wrongItem = \App\Models\OrderItem::where('order_id', $order->id)
    ->where('product_variant_id', 26)
    ->where('price', 0)
    ->first();

if (!$wrongItem) {
    echo "Wrong gift item not found in this order.\n";
    exit;
}

// The correct product was product_id = 26 (100% Sôcôla)
$correctProduct = \App\Models\Product::with('variants', 'volumeMedia')->find(26);
if (!$correctProduct) {
    echo "Correct product (ID 26) not found in DB.\n";
    exit;
}

// Get the first variant of the correct product
$correctVariant = $correctProduct->variants->first();
if (!$correctVariant) {
    echo "Correct product does not have any variants.\n";
    exit;
}

// Get the correct image
$volumeMedia = $correctProduct->volumeMedia()->where('volume_id', $correctVariant->volume_id)->first();
$image = $correctVariant->main_image ?? ($volumeMedia ? $volumeMedia->main_image : $correctProduct->main_image);

// Update the order item
$wrongItem->update([
    'product_variant_id' => $correctVariant->id,
    'product_name' => $correctProduct->name,
    'variant_name' => $correctVariant->name,
    'image' => $image,
    'volume' => $correctVariant->volume->name ?? null,
    'packing_type' => $correctVariant->packagingType->name ?? null,
    // Original price of the correct item
    'original_price' => $correctVariant->base_price ?: $correctVariant->price,
]);

echo "Successfully updated order item!\n";
echo "New Product: " . $correctProduct->name . "\n";
echo "New Variant ID: " . $correctVariant->id . "\n";
echo "New Image: " . $image . "\n";
