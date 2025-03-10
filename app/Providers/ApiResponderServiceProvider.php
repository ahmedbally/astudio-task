<?php

namespace App\Providers;

use App\Support\ApiResponder\ApiResponder;
use App\Support\ApiResponder\Exceptions\Handler;
use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;
use App\Support\ApiResponder\Factories\ResponseFactory;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

/**
 * Service Provider for the API Responder
 */
class ApiResponderServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register the Response Factory
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory();
        });

        // Register the Responder
        $this->app->singleton(ApiResponder::class, function ($app) {
            return new ApiResponder($app->make(ResponseFactoryContract::class));
        });

        // Register the Exception Handler
        $this->app->singleton(ExceptionHandlerContract::class, Handler::class);

    }
}
