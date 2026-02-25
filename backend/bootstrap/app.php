<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        // apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        // Add global middleware
        // Trust proxies first (for Cloudflare)
        // $middleware->append(\App\Http\Middleware\TrustProxies::class);
        // Add cache headers for Cloudflare CDN
        // $middleware->append(\App\Http\Middleware\CloudflareCacheHeaders::class);
        // Compress responses
        $middleware->append(\App\Http\Middleware\CompressResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
