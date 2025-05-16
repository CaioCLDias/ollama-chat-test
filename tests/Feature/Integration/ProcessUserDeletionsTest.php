<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProcessUserDeletionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_users_scheduled_for_deletion()
    {
        $deletableUser = User::factory()->create([
            'scheduled_for_deletion_at' => now()->subDay(),
        ]);

        $nonDeletableUser = User::factory()->create([
            'scheduled_for_deletion_at' => now()->addDay(),
        ]);

        
        $deletableUser->createToken('TestToken');

    
        $this->artisan('users:process-user-deletions')
            ->expectsOutput("User {$deletableUser->id} deleted successfully.")
            ->assertExitCode(0);

        $this->assertSoftDeleted('users', ['id' => $deletableUser->id]);

        $this->assertDatabaseHas('users', ['id' => $nonDeletableUser->id]);
    }
}
