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

$items = \App\Models\OrderItem::where('order_id', $order->id)->get();
echo "Order Items:\n";
foreach ($items as $item) {
    echo json_encode($item->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}
