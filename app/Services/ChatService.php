<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Utils\OllamaStreamParser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ChatService
{

    protected string $ollamaEndpoint;

    public function __construct()
    {
       $this->ollamaEndpoint = config('services.ollama.url') . '/api/generate';
    }

    public function sendMessage(int $userId, string $message): array
    {
        $response = Http::withOptions(['stream' => true])
            ->post($this->ollamaEndpoint, [
                'model' => 'llama3.2:1b',
                'prompt' => $message,
            ]);

        $content = OllamaStreamParser::extractTextFromStream($response->getBody());

        if (empty($content)) {
            Log::error('Ollama stream returned no usable content.', [
                'stream_head' => substr((string) $response->getBody(), 0, 512),
            ]);
            throw new \Exception('No response from Ollama stream.');
        }

        $this->saveChatHistory($userId, $message, $content);

        return [
            'message' => $message,
            'response' => $content,
        ];
    }

    protected function saveChatHistory(int $userId, string $message, string $response): void
    {
        ChatHistory::create([
            'user_id' => $userId,
            'message' => $message,
            'response' => $response,
        ]);
    }
}
