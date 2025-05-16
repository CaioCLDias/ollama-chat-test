<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_send_chat_message()
    {

        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('TestToken')->plainTextToken;

        Http::fake(function ($request) {
            return Http::response([
                'response' => 'Hello, how can I help you?',
                'done' => true,
            ]);
        });

        $response = $this->withToken($token)
            ->postJson('/api/chat', [
                'message' => 'Hello, how are you?',
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Chat message sent successfully',
                'data' => [
                    'message' => 'Hello, how are you?',
                    'response' => 'Hello, how can I help you?',
                ],
            ]);

        $this->assertDatabaseHas('chat_histories', [
            'user_id' => $user->id,
            'message' => 'Hello, how are you?',
            'response' => 'Hello, how can I help you?',
        ]);
    }

    public function test_guest_user_cannot_access_chat()
    {
        $response = $this->postJson('/api/chat', [
            'message' => 'Test message',
        ]);

        $response->assertUnauthorized();
    }
}
