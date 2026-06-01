<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('GEMINI_API_KEY');
echo "API Key: " . ($apiKey ? 'Set' : 'Not set') . "\n\n";

// List of common Gemini models to test
$models = [
    'gemini-1.5-flash',
    'gemini-1.5-flash-8b',
    'gemini-1.5-flash-latest',
    'gemini-1.5-pro',
    'gemini-1.5-pro-latest',
    'gemini-flash-latest',
    'gemini-flash',
    'gemini-pro',
    'gemini-pro-latest',
    'gemini-1.0-pro',
    'gemini-1.0-pro-vision',
    'gemini-1.5-flash-001',
    'gemini-1.5-pro-001',
    'models/gemini-1.5-flash',
    'models/gemini-1.5-pro',
    'v1beta/models/gemini-1.5-flash',
    'v1beta/models/gemini-1.5-pro',
];

foreach ($models as $model) {
    echo "Testing model: {$model}... ";
    
    try {
        $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Hello']]]
            ],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 50]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
            echo "✅ SUCCESS - Response: " . substr($reply, 0, 50) . "...\n";
        } else {
            echo "❌ FAILED - Status: " . $response->status() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\nHoàn tất testing!\n";
