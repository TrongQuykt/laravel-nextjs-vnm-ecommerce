<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::create('/api/v1/home', 'GET')
);

$data = json_decode($response->getContent(), true);
$featured = collect($data['featured_products'] ?? [])->where('id', 17)->first();

echo "Product 17 (Featured):\n";
echo "Home Featured Volume ID: " . $featured['home_featured_volume_id'] . "\n";
echo "Main Image: " . $featured['main_image'] . "\n";
echo "Featured Variant images[0]: " . ($featured['home_featured_variant']['images'][0] ?? 'N/A') . "\n";
echo "Featured Variant main_image: " . ($featured['home_featured_variant']['main_image'] ?? 'N/A') . "\n";
