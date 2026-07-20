<?php

use Illuminate\Auth\AuthenticationException;
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
    ->withBroadcasting(__DIR__.'/../routes/channels.php', [
        'middleware' => ['auth:sanctum'],
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Adicionar middleware de CORS globalmente
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);

        // API-only app: never resolve a named "login" route (throws RouteNotFoundException).
        $middleware->redirectGuestsTo(function (Request $request) {
            // #region agent log
            $payload = json_encode([
                'sessionId' => '6d48b3',
                'runId' => 'post-fix',
                'hypothesisId' => 'H2-redirectGuestsTo',
                'location' => 'bootstrap/app.php:redirectGuestsTo',
                'message' => 'guest redirect bypassed for API',
                'data' => [
                    'path' => $request->path(),
                    'accept' => $request->header('Accept'),
                    'isApi' => $request->is('api/*'),
                ],
                'timestamp' => (int) (microtime(true) * 1000),
            ])."\n";
            @file_put_contents(storage_path('logs/debug-6d48b3.log'), $payload, FILE_APPEND);
            // #endregion

            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API routes must never redirect to a named web "login" route.
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // #region agent log
            $payload = json_encode([
                'sessionId' => '6d48b3',
                'runId' => 'post-fix',
                'hypothesisId' => 'H1-login-route',
                'location' => 'bootstrap/app.php:unauthenticated',
                'message' => 'API unauthenticated handled as JSON',
                'data' => [
                    'path' => $request->path(),
                    'accept' => $request->header('Accept'),
                    'expectsJson' => $request->expectsJson(),
                    'isApi' => $request->is('api/*'),
                ],
                'timestamp' => (int) (microtime(true) * 1000),
            ])."\n";
            @file_put_contents(storage_path('logs/debug-6d48b3.log'), $payload, FILE_APPEND);
            // #endregion

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage() ?: 'Unauthenticated.'], 401);
            }
        });
    })->create();
