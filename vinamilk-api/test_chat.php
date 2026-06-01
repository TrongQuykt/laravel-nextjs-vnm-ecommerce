<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('GEMINI_API_KEY');
echo "API Key: " . ($apiKey ? 'Set' : 'Not set') . "\n";

$settings = \App\Models\ChatSetting::whereIn('key', ['system_instruction', 'gemini_model'])->get()->keyBy('key');
echo "System Instruction: " . ($settings->get('system_instruction')?->value ?? 'Not found') . "\n";
echo "Model Name: " . ($settings->get('gemini_model')?->value ?? 'Not found') . "\n";

// Test API call
try {
    $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => 'Hello']]]
        ],
        'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 100]
    ]);

    echo "API Response Status: " . $response->status() . "\n";
    echo "API Response Body: " . $response->body() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
