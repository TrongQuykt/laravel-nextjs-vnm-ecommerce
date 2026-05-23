<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::where('order_number', 'ES-260518192141IGEL')->first();

$state = $order->shipping_method_name;

$name = $state ?: 'Giao hàng tiêu chuẩn';
if ($order->status === 'shipping' || $order->status === 'completed' || $order->tracking_number) {
    $result = "Giao Hàng Nhanh (GHN) - " . $name;
} else {
    $result = $name;
}

echo "Order Number: {$order->order_number}\n";
echo "State variable: " . var_export($state, true) . "\n";
echo "Status: {$order->status}\n";
echo "Tracking: {$order->tracking_number}\n";
echo "Formatted Result: '{$result}'\n";
