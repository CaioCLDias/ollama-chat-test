<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FullUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_user_can_register_verify_login_chat_and_delete_account()
    {
        Notification::fake();

        // 1. Register
        $response = $this->postJson('/api/user', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasVerifiedEmail());

        // 2. Email Verification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $user->markEmailAsVerified();
        $user->refresh();

        // 3. Login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $token = $response->json('data.access_token');
        $this->assertNotEmpty($token);

        // 4. Access Chat
        $chatResponse = $this->withToken($token)->postJson('/api/chat', [
            'message' => 'Hello, how are you?',
        ]);

        $chatResponse->assertOk();
        $this->assertDatabaseHas('chat_histories', [
            'user_id' => $user->id,
            'message' => 'Hello, how are you?',
        ]);

        // 5. Schedule deletion
        $this->withToken($token)->deleteJson('/api/user/delete')->assertOk();
        $this->assertNotNull($user->fresh()->scheduled_for_deletion_at);

        // 6. Run deletion command
        $user->scheduled_for_deletion_at = now()->subMinute();
        $user->save();
        Artisan::call('users:process-user-deletions');

        // 7. Assert user soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
