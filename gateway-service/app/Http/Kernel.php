<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * @var array<string, class-string>
     */
    protected $routeMiddleware = [
        'correlation' => \App\Http\Middleware\CorrelationMiddleware::class,
    ];
    protected $middleware = [
        \App\Http\Middleware\CorrelationMiddleware::class,
    ];
}
