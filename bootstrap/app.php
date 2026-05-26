<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
<<<<<<< HEAD
=======
            'jwt' => \App\Http\Middleware\JWTAuthenticate::class,
>>>>>>> 6e8e425cc398210b56a9a3422f2e38cd2169470d
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
