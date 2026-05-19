<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: [
            __DIR__ . '/../routes/api.php',
            __DIR__ . '/../routes/warehouse.php',
            __DIR__ . '/../routes/fixedasset.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )


    //MIDDLEWARE
    ->withMiddleware(function (Middleware $middleware): void {

        //EVITA redirect a route('login')
        $middleware->redirectGuestsTo(fn() => null);
    })

    //EXCEPCIONES
    ->withExceptions(function (Exceptions $exceptions): void {

        //FORZAR JSON SIEMPRE (API FIRST)
        $exceptions->shouldRenderJsonWhen(fn() => true);

        //NO AUTENTICADO (token faltante / inválido)
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado o inválido.',
            ], 401);
        });

        //NO AUTORIZADO (roles / permisos)
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.',
            ], 403);
        });
    })

    ->create();
