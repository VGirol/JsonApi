<?php

namespace VGirol\JsonApi\Tests;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\JsonApiPaginate\JsonApiPaginateServiceProvider;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use VGirol\JsonApi\JsonApiServiceProvider;
use VGirol\JsonApi\Testing\TestingTools;
use VGirol\JsonApiAssert\Laravel\JsonApiAssertServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use TestingTools;

    /**
     * Setup DB before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(JsonApiPaginateServiceProvider::class);
        $this->app->register(QueryBuilderServiceProvider::class);
        $this->app->register(JsonApiAssertServiceProvider::class);

        config(['app.debug' => true]);
    }

    /**
     * Load package service provider
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            JsonApiServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'request' => 'Illuminate\Http\Request'
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function buildRoutes($groups)
    {
        foreach ($groups as $group) {
            Route::jsonApiResource(
                $group['route'],
                null,
                ['relationships' => true]
            );
        }

        $this->refreshRouter();
    }
}
