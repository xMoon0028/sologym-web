<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all reverse proxies (ngrok, Cloudflare, load balancers)
        $middleware->trustProxies(at: '*');

        // Register Spatie role/permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Auto-redirect authenticated users to their correct dashboard inside web group
        $middleware->web(append: [
            \App\Http\Middleware\RoleRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Gracefully handle UnauthorizedException (role/permission failure)
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if (auth()->check()) {
                if ($request->is('admin*')) {
                    return redirect()->route('portal.dashboard')->with('error', 'You do not have administrative permissions to access that page.');
                }
                return redirect()->route('portal.dashboard');
            }
            return redirect()->route('login')->with('error', 'Please sign in to access this page.');
        });
    })->create();
