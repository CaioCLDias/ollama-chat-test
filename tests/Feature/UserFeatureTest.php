<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();


        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->token = $this->user->createToken('TestToken')->plainTextToken;
    }

    public function test_user_can_register()
    {
        $email = $this->faker->unique()->safeEmail();

        $response = $this->postJson('/api/user', [
            'name' => $this->faker->name(),
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
    }


    public function test_user_can_show()
    {

        $response = $this->withToken($this->token)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                ],
            ]);
    }

    public function test_user_can_update()
    {

        $response = $this->withToken($this->token)
            ->putJson('/api/user', [
                'name' => 'Updated Name',
                'email' => 'test@email.com'
            ]);


        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                ],
            ]);

        $this->assertEquals('Updated Name', $this->user->fresh()->name);
    }

    function test_user_can_delete()
    {

        $response = $this->withToken($this->token)
            ->deleteJson("/api/user/delete");

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Account deletion scheduled',
            ]);

        $this->assertNotNull($this->user->fresh()->scheduled_for_deletion_at);
    }

    public function test_user_can_cancel_deletion()
    {

        $response = $this->withToken($this->token)
            ->postJson('/api/user/cancel-deletion');

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Account deletion cancelled.',
            ]);

        $this->assertNull($this->user->fresh()->scheduled_for_deletion_at);
    }
}
