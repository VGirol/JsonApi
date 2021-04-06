<?php

namespace VGirol\JsonApi\Tests;

use Orchestra\Testbench\Concerns\WithFactories;

trait UsesTools
{
    use WithFactories;

    // /**
    //  * Setup before each test.
    //  *
    //  * @return void
    //  */
    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     $this->setUpTools(true);
    // }

    /**
     * Setup routes, aliases and DB before each test.
     *
     * @param bool $reset
     *
     * @return void
     */
    protected function setUpTools(bool $reset = true): void
    {
        $this->setUpToolsAliases($reset);
        $this->setUpToolsRoutes();
        $this->setUpToolsModels();
        $this->setUpToolsDB();
    }

    protected function setUpToolsModels(): void
    {
        $this->withFactories(__DIR__ . '/Tools/factories');
    }

    protected function setUpToolsRoutes()
    {
        // Add test routes
        require(__DIR__ . '/Tools/routes/routes.php');

        $this->refreshRouter();
    }

    protected function setUpToolsAliases(bool $reset)
    {
        // Set config
        $aliases = $this->getToolsAliases();
        $this->setConfigAliases($aliases, $reset);
    }

    protected function setUpToolsDB(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Tools/migrations');
    }

    private function getToolsAliases()
    {
        return [
            [
                'type' => 'photo',
                'route' => 'photos',
                'model' => \VGirol\JsonApi\Tests\Tools\Models\Photo::class,
                'request' => \VGirol\JsonApi\Tests\Tools\Requests\PhotoFormRequest::class
            ],
            [
                'type' => 'price',
                'route' => 'prices',
                'model' => \VGirol\JsonApi\Tests\Tools\Models\Price::class,
                'request' => \VGirol\JsonApi\Tests\Tools\Requests\PriceFormRequest::class
            ],
            [
                'type' => 'tag',
                'route' => 'tags',
                'model' => \VGirol\JsonApi\Tests\Tools\Models\Tags::class,
                'request' => \VGirol\JsonApi\Tests\Tools\Requests\TagFormRequest::class
            ],
            [
                'type' => 'comment',
                'route' => 'comments',
                'model' => \VGirol\JsonApi\Tests\Tools\Models\Comment::class,
                'request' => \VGirol\JsonApi\Tests\Tools\Requests\CommentFormRequest::class,
                'relationships' => [
                    // 'user' => \VGirol\JsonApi\Tests\Tools\Models\Author::class
                    'user' => 'author'
                ]
            ],
            [
                'type' => 'author',
                'route' => 'authors',
                'model' => \VGirol\JsonApi\Tests\Tools\Models\Author::class,
                'request' => \VGirol\JsonApi\Tests\Tools\Requests\AuthorFormRequest::class,
                'resource-ro' => \VGirol\JsonApi\Tests\Tools\Resources\AuthorResource::class
            ]
        ];
    }
}
