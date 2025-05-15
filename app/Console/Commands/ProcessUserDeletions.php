<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ProcessUserDeletions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:process-user-deletions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users whose accounts were scheduled for deletion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNotNull('scheduled_for_deletion_at')
            ->where('scheduled_for_deletion_at', '<=', now())
            ->get();

        foreach ($users as $user) {
            $user->tokens()->delete();
            $user->delete();
            $this->info("User {$user->id} deleted successfully.");
        }

        return Command::SUCCESS;
    }
}
