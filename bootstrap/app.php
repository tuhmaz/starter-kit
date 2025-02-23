<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\SwitchDatabase;
use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\UpdateUserLastActivity;
use App\Http\Middleware\PerformanceOptimizer;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VisitorTrackingMiddleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(LocaleMiddleware::class);
    $middleware->web(CompressResponse::class);
    $middleware->web(UpdateUserLastActivity::class);
    $middleware->web(PerformanceOptimizer::class);
    $middleware->web(SecurityHeaders::class);
    $middleware->web(SwitchDatabase::class);
    $middleware->web(VisitorTrackingMiddleware::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
