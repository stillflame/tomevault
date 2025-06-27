<?php

use App\Http\Middleware\ApiLoggingMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->appendToGroup('api', [
            ApiLoggingMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(static function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'The resource you requested was not found.',
                    'status' => 404,
                ], 404);
            }

            return null;
        });
    })
    ->create();

// ✅ Register custom provider before returning the app
$app->register(App\Providers\RouteBindingServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class); // Add this line

return $app;
