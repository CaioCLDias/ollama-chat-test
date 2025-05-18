<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {


        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            \Illuminate\Http\Middleware\TrustHosts::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \Illuminate\Http\Middleware\TrustProxies::class,
        ]);


        $middleware->group('api', [
            \App\Http\Middleware\StripCsrfTokenHeader::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);


        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);


        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'deleted' => \App\Http\Middleware\EnsureDeletedUsers::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('users:process-user-deletions')->hourly()->timezone('UTC')->appendOutputTo(storage_path('logs/deletions.log'));
        $schedule->command('chat:update-main-message')->daily()->timezone('UTC')->appendOutputTo(storage_path('logs/chat.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
