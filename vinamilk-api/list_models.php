<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('GEMINI_API_KEY');
echo "API Key: " . ($apiKey ? 'Set' : 'Not set') . "\n\n";

// Try to list available models
echo "Đang lấy danh sách models có sẵn...\n";

try {
    $response = \Illuminate\Support\Facades\Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
    
    echo "Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "Danh sách models:\n";
        
        if (isset($data['models'])) {
            foreach ($data['models'] as $model) {
                echo "- " . $model['name'] . "\n";
                if (isset($model['displayName'])) {
                    echo "  Display: " . $model['displayName'] . "\n";
                }
            }
        } else {
            echo "Không tìm thấy models trong response\n";
            print_r($data);
        }
    } else {
        echo "Lỗi: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
