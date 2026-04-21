<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->get('/download-progress', [\App\Http\Controllers\NubeController::class, 'downloadProgress'])
                ->name('download.progress');

            Route::middleware('web')
                ->group(base_path('routes/user.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('nubes.index'));
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnection::class,
        ]);
        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
