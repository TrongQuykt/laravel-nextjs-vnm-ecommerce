<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\Order::orderBy('created_at', 'desc')->take(5)->get();
foreach ($orders as $order) {
    echo "Order: {$order->order_number} | Delivery Type: {$order->delivery_type} | Method ID: " . json_encode($order->shipping_method_id) . " | Method Name: '{$order->shipping_method_name}'\n";
}
