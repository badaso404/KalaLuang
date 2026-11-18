<?php

use App\Http\Middleware\EnsureCompanyIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RecordVisit;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'company.verified' => EnsureCompanyIsVerified::class,
        ]);

        $middleware->web(append: [
            RecordVisit::class,
        ]);

        /**
         * Laravel Cloud terminates TLS at its load balancer, so without this
         * every request would look like it came from the proxy.
         */
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
