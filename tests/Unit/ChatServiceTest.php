<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;


    public function test_send_message_saves_history_and_returns_response()
    {
        $user = User::factory()->create();

        Http::fake([
            'http://ollama:11434/api/generate' => Http::response([
                'response' => 'Mocked response from Ollama.',
                'done' => true
            ], 200),
        ]);

        $service = new ChatService();

        $result = $service->sendMessage($user->id, 'Test message to Ollama');

        $this->assertEquals('Test message to Ollama', $result['message']);
        $this->assertEquals('Mocked response from Ollama.', $result['response']);

        $this->assertDatabaseHas('chat_histories', [
            'user_id' => $user->id,
            'message' => 'Test message to Ollama',
            'response' => 'Mocked response from Ollama.',
        ]);
    }
    
}
