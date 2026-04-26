<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\TenancyServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ResolveTenant corre antes que auth en web y API
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveTenant::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ResolveTenant::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role'          => \App\Http\Middleware\CheckRole::class,
            'check.branch'  => \App\Http\Middleware\CheckBranchAccess::class,
            'resolve.tenant'=> \App\Http\Middleware\ResolveTenant::class,
        ]);

        // Excluir /api/login de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'api/login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar errores de autenticación en API
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'No autenticado. Por favor, proporcione un token de acceso válido.'
                ], 401);
            }
        });
    })->create();
