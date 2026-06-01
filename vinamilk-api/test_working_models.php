<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = env('GEMINI_API_KEY');
echo "API Key: " . ($apiKey ? 'Set' : 'Not set') . "\n\n";

// Test the most common models from the available list
$models = [
    'models/gemini-flash-latest',
    'models/gemini-2.0-flash',
    'models/gemini-2.5-flash',
    'models/gemini-2.0-flash-lite',
    'models/gemini-pro-latest',
];

foreach ($models as $model) {
    echo "Testing model: {$model}... ";
    
    try {
        $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => 'Xin chào, bạn là ai?']]]
            ],
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 100]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
            echo "✅ SUCCESS\n";
            echo "   Response: " . substr($reply, 0, 100) . "...\n\n";
        } else {
            echo "❌ FAILED - Status: " . $response->status() . "\n";
            echo "   Error: " . $response->body() . "\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ ERROR - " . $e->getMessage() . "\n\n";
    }
}

echo "Hoàn tất testing!\n";
