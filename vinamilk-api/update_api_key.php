<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update .env file
$envPath = __DIR__ . '/.env';
$envContent = file_get_contents($envPath);

// Replace API key with old one
$oldApiKey = 'AIzaSyDQCFl3z0z_sALoRSAybHoh6ZonR0afvmM';
$envContent = preg_replace('/GEMINI_API_KEY=.*/', 'GEMINI_API_KEY=' . $oldApiKey, $envContent);

file_put_contents($envPath, $envContent);

echo "Đã cập nhật API key cũ trong file .env\n";
echo "Vui lòng restart server backend để áp dụng thay đổi\n";
