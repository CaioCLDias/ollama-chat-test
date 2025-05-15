<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class ChatUpdateMainMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:update-main-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the main message of the chat model (placeholder command).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        
        $timestamp = now()->toDateTimeString();

        Log::info("[Chat] update main message command executed at: $timestamp");

        $this->info('Chat main message updated successfully.');

        return Command::SUCCESS;
        
    }
}
