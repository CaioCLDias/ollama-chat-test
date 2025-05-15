<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid) {
            return $isValid && $accessToken->tokenable && is_null($accessToken->tokenable->deleted_at);
        });
    }
}
