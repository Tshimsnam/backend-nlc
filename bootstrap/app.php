<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Le site passe par un relais Vercel : garder l'IP du visiteur pour les limites de débit
        // et le HTTPS d'origine. L'hôte transmis reste ignoré, le backend génère ses propres URL.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
            'admin.session' => \App\Http\Middleware\AdminSession::class,
            'maxicash.signature' => \App\Http\Middleware\VerifyMaxiCashSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
