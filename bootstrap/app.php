<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsurePasswordChangeCompleted;
use App\Http\Middleware\EnsureActivePortalAccount;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/contractor.php',
            __DIR__.'/../routes/warden.php',
            __DIR__.'/../routes/admin.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(__DIR__.'/../routes/channels.php', [
        'middleware' => ['web', 'auth:student', EnsureActivePortalAccount::class],
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(
            fn (Request $request): string => match (true) {
                $request->is('contractor/*') => route('contractor.login'),
                $request->is('warden/*') => route('warden.login'),
                $request->is('student/*') => route('student.portal'),
                default => route('login'),
            },
        );

        $middleware->alias([
            'role' => EnsureRole::class,
            'portal.account.active' => EnsureActivePortalAccount::class,
            'password.change.completed' => EnsurePasswordChangeCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
