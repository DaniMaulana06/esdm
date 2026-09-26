<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminOrStaf;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OperatorBku;
use App\Http\Middleware\StafEsdm;
use App\Http\Middleware\StafOrOperatorBku;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'staf_esdm' => StafEsdm::class,
            'operator-bku' => OperatorBku::class,
            'admin' => Admin::class,
            'admin-or-staf' => AdminOrStaf::class,
            'staf-or-operator-bku' => StafOrOperatorBku::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
