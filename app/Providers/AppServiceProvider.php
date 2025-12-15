<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Configuración de Laravel Passport (OAuth 2.0)

        // Configurar expiración de tokens
        Passport::tokensExpireIn(now()->addDays(15));        // Access token expira en 15 días
        Passport::refreshTokensExpireIn(now()->addDays(30)); // Refresh token expira en 30 días
        Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Token personal expira en 6 meses

        // Habilitar Password Grant (para login con email/password)
        Passport::enablePasswordGrant();
    }
}
