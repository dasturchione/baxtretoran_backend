<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof RouteNotFoundException) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $e->errors(),
                ], 422);
            }

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        });
    })->create();
