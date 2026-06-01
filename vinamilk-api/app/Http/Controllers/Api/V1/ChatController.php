<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array'
        ]);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return response()->json(['error' => 'API Key missing'], 500);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $sessionId = $request->header('X-Chat-Session-Id', 'default-session');

        // 1. Get Personality and Model from DB
        $settings = \App\Models\ChatSetting::whereIn('key', ['system_instruction', 'gemini_model'])->get()->keyBy('key');
        
        $systemInstruction = $settings->get('system_instruction')?->value ?? "Bạn là trợ lý ảo Vinamilk.";
        $modelName = $settings->get('gemini_model')?->value ?? "gemini-flash-latest";

        // 2. Knowledge Base Search
        $knowledgeContext = "";
        $knowledges = \App\Models\ChatKnowledge::where('is_active', true)
            ->where(function($q) use ($userMessage) {
                $q->where('question', 'like', "%{$userMessage}%")
                  ->orWhere('answer', 'like', "%{$userMessage}%");
            })->limit(3)->get();

        if ($knowledges->count() > 0) {
            $knowledgeContext = "\nKiến thức từ doanh nghiệp:\n";
            foreach ($knowledges as $k) {
                $knowledgeContext .= "- {$k->question}: {$k->answer}\n";
            }
        }

        // 3. Save User Message
        \App\Models\ChatMessage::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $userMessage,
            'ip_address' => $request->ip()
        ]);

        $contents = [];
        foreach ($history as $chat) {
            $contents[] = [
                'role' => $chat['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $chat['content']]]
            ];
        }

        $finalPrompt = $systemInstruction . $knowledgeContext . "\n\nUser: " . $userMessage;
        $contents[] = ['role' => 'user', 'parts' => [['text' => $finalPrompt]]];

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}", [
                'contents' => $contents,
                'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 800]
            ]);

            if ($response->failed()) return response()->json(['error' => 'Bot bận'], 500);

            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Em chưa hiểu ý anh/chị.';

            // 4. Save Bot Reply
            \App\Models\ChatMessage::create([
                'session_id' => $sessionId,
                'role' => 'bot',
                'content' => $reply,
                'ip_address' => $request->ip()
            ]);

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi kết nối'], 500);
        }
    }
}
