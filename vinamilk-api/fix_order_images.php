<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\OrderItem::whereNull('image')->orWhere('image', '')->get();
$count = 0;

foreach ($items as $item) {
    if ($item->product_variant_id) {
        $variant = \App\Models\ProductVariant::with(['product.volumeMedia'])->find($item->product_variant_id);
        if ($variant) {
            $volumeMedia = $variant->product->volumeMedia()->where('volume_id', $variant->volume_id)->first();
            $image = $variant->main_image ?? ($volumeMedia ? $volumeMedia->main_image : $variant->product->main_image);
            if ($image) {
                $item->update(['image' => $image]);
                $count++;
            }
        }
    }
}
echo "Fixed images for $count order items.\n";

$p26 = \App\Models\Product::find(26);
echo "Original Product 26 name: " . ($p26 ? $p26->name : "Not found") . "\n";
