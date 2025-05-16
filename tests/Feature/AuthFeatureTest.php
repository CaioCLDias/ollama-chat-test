<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email']
                ],
            ])
            ->assertJson([
                'status' => true,
                'message' => 'Login successful.',
            ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson(['status' => true, 'message' =>  'Logout successful.']);
    }

    public function test_user_can_request_password_reset_link()
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'message'])
            ->assertJson(['status' => true, 'message' => trans(Password::RESET_LINK_SENT)]);
    }

    public function test_user_can_reset_password()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'message'])
            ->assertJson(['status' => true, 'message' => trans(Password::PASSWORD_RESET)]);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_user_can_verify_email()
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(config('app.frontend_url') . '/email-verified-success');
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_resend_verification_email()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/email/verification-notification');

        $response->assertOk()
            ->assertJsonStructure(['status', 'message'])
            ->assertJson(['status' => true, 'message' => 'Verification link sent']);
    }

    public function test_user_can_revocer_account()
    {
        $email = 'recover@email.com';

        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
            'scheduled_for_deletion_at' => now()->subDay(), // <- Correção aqui
        ]);

        $user->delete(); // Soft delete

        $this->assertSoftDeleted('users', ['email' => $email]);

        $response = $this->postJson('/api/auth/recover-account', [
            'email' => $email,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Account recovered successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'deleted_at' => null,
            'scheduled_for_deletion_at' => null,
        ]);
    }
}
