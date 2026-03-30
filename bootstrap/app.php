<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('client*')) return '/client/login';
            if ($request->is('staff*')) return '/staff/login';
            if ($request->is('accountant*')) return '/accountant/login';
            if ($request->is('marketing*')) return '/marketing/login';
            return '/admin/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Redirect AFK users back to login instead of showing 419 error
            return redirect()->guest(
                $request->is('client*') ? '/client/login' :
                ($request->is('staff*') ? '/staff/login' : '/admin/login')
            );
        });

        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(
                $request->is('client*') ? '/client/login' :
                ($request->is('staff*') ? '/staff/login' :
                ($request->is('accountant*') ? '/accountant/login' :
                ($request->is('marketing*') ? '/marketing/login' : '/admin/login')))
            );
        });
    })->create();
