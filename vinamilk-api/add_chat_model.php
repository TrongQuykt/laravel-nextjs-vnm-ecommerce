<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Add gemini_model setting if it doesn't exist
$existing = \App\Models\ChatSetting::where('key', 'gemini_model')->first();

if (!$existing) {
    \App\Models\ChatSetting::create([
        'key' => 'gemini_model',
        'description' => 'Tên model API Gemini',
        'value' => 'gemini-flash-latest'
    ]);
    echo "Đã thêm cấu hình gemini_model\n";
} else {
    echo "Cấu hình gemini_model đã tồn tại\n";
}

// Check system_instruction
$systemInstruction = \App\Models\ChatSetting::where('key', 'system_instruction')->first();
if (!$systemInstruction) {
    \App\Models\ChatSetting::create([
        'key' => 'system_instruction',
        'description' => 'Hướng dẫn hệ thống (System Instruction)',
        'value' => 'Bạn là trợ lý ảo Vinamilk, chuyên hỗ trợ khách hàng về các sản phẩm sữa, dinh dưỡng và dịch vụ của Vinamilk.'
    ]);
    echo "Đã thêm cấu hình system_instruction\n";
} else {
    echo "Cấu hình system_instruction đã tồn tại\n";
}

echo "Hoàn tất!\n";
