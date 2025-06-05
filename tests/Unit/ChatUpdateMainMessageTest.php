<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Log;
use Tests\TestCase;

class ChatUpdateMainMessageTest extends TestCase
{
   
    public function test_it_logs_successfully_when_command_runs()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, '[Chat] update main message command executed at');
            });

        $exitCode = Artisan::call('chat:update-main-message');

        $this->assertEquals(0, $exitCode);
    }
}
