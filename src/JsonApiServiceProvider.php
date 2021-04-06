<?php

namespace VGirol\JsonApi;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use VGirol\JsonApi\Commands\AliasCommand;
use VGirol\JsonApi\Commands\MakeAllCommand;
use VGirol\JsonApi\Commands\MakeControllerCommand;
use VGirol\JsonApi\Commands\MakeModelCommand;
use VGirol\JsonApi\Commands\MakeRequestCommand;
use VGirol\JsonApi\Commands\MakeResourceCommand;
use VGirol\JsonApi\Commands\MakeResourcesCommand;
use VGirol\JsonApi\Commands\RouteCommand;
use VGirol\JsonApi\Exceptions\JsonApiHandler;
use VGirol\JsonApi\Middleware\AddResponseHeaders;
use VGirol\JsonApi\Middleware\CheckQueryParameters;
use VGirol\JsonApi\Middleware\CheckRequestHeaders;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Services\ExceptionService;
use VGirol\JsonApi\Services\FieldsService;
use VGirol\JsonApi\Services\FilterService;
use VGirol\JsonApi\Services\IncludeService;
use VGirol\JsonApi\Services\ModelService;
use VGirol\JsonApi\Services\PaginationService;
use VGirol\JsonApi\Services\SortService;
use VGirol\JsonApi\Services\ValidateService;

class JsonApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Commands & Config
        if ($this->app->runningInConsole()) {
            $this->commands([
                AliasCommand::class,
                MakeAllCommand::class,
                MakeControllerCommand::class,
                MakeModelCommand::class,
                MakeResourceCommand::class,
                MakeResourcesCommand::class,
                MakeRequestCommand::class,
                RouteCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/config/jsonapi.php' => config_path('jsonapi.php'),
                __DIR__ . '/config/jsonapi-alias.php' => config_path('jsonapi-alias.php'),
            ], 'config');
        }

        // Config
        $this->mergeConfigFrom(
            __DIR__ . '/config/jsonapi.php',
            'jsonapi'
        );

        // Middlewares
        // Add middlewares to router
        $router = $this->app['router'];
        if (!$router->hasMiddlewareGroup('jsonapi')) {
            $router->middlewareGroup('jsonapi', []);
        }
        // $router->aliasMiddleware('jsonapi.addHeaders', AddResponseHeaders::class);
        // $router->pushMiddlewareToGroup('jsonapi', 'jsonapi.addHeaders');
        $router->aliasMiddleware('jsonapi.checkHeaders', CheckRequestHeaders::class);
        $router->pushMiddlewareToGroup('jsonapi', 'jsonapi.checkHeaders');
        $router->aliasMiddleware('jsonapi.checkQuery', CheckQueryParameters::class);
        $router->pushMiddlewareToGroup('jsonapi', 'jsonapi.checkQuery');

        // Add middlewares to kernel (globally)
        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware(AddResponseHeaders::class);

        // Macros
        foreach (glob(__DIR__ . '/macro/*.php') as $file) {
            require_once $file;
        }

        // Routes
        app('router')->patterns(['id' => '[0-9]+', 'parentId' => '[0-9]+']);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(ExceptionService::class);
        $this->app->singleton(SortService::class);
        $this->app->singleton(FilterService::class);
        $this->app->singleton(IncludeService::class);
        $this->app->singleton(PaginationService::class);
        $this->app->singleton(AliasesService::class);
        $this->app->singleton(FieldsService::class);
        $this->app->singleton(ExceptionHandler::class, JsonApiHandler::class);

        $this->app->bind(ModelService::class);
        $this->app->bind(ValidateService::class);
    }
}
