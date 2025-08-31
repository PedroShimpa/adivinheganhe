<?php

use App\Http\Middleware\CheckBanned;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        // health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // cria um apelido "banned" para o middleware
        $middleware->alias([
            'banned' => CheckBanned::class,
            'isAdmin' => IsAdminMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
