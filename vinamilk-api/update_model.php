<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update gemini_model setting
$setting = \App\Models\ChatSetting::where('key', 'gemini_model')->first();
if ($setting) {
    $setting->value = 'gemini-flash-latest';
    $setting->save();
    echo "Đã cập nhật model thành gemini-flash-latest\n";
} else {
    echo "Không tìm thấy cấu hình gemini_model\n";
}
