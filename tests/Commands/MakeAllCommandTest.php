<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeAllCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createAllFilesProvider
     */
    public function createAllFiles(array $params, array $expectedFiles)
    {
        $this->artisan('make:jsonapi:all', $params)
             ->assertExitCode(0);

        foreach ($expectedFiles as $key => $expected) {
            $this->assertDiskHas($expected['path']);

            switch ($key) {
                case 'controller':
                    $this->assertControllerFileContains($expected['path'], $expected['namespace'], $expected['class']);
                    break;
                case 'request':
                    $this->assertFormRequestFileContains($expected['path'], $expected['namespace'], $expected['class']);
                    break;
                case 'resource':
                    $this->assertResourceFileContains($expected['path'], $expected['namespace'], $expected['class'], $expected['base-class']);
                    break;
                case 'alias':
                    $this->assertAliasConfigFileContains($expected['path'], $expected['content']);
                    break;
                case 'routes':
                    $this->assertRoutesFileContains($expected['path'], $expected['content']);
                    break;
            }
        }
    }

    public function createAllFilesProvider()
    {
        $basePathModels = '/app/Models';
        $basePathFactories = '/database/factories';
        $basePathMigrations = '/database/migrations';
        $basePathSeeds = '/database/seeders';
        $basePathRequests = '/app/Http/Requests';
        $basePathControllers = '/app/Http/Controllers';
        $basePathResources = '/app/Http/Resources';

        return [
            'without namespace' => [
                [
                    'name' => 'Dummy',
                    'type' => 'dummy-type',
                    'route' => 'dummy.route',
                    '--factory' => true,
                    '--jsonapi-controller' => true,
                    '--jsonapi-request' => true,
                    '--jsonapi-resources' => true,
                    '--types' => 'ro'
                ],
                [
                    'model' => [
                        'path' => "{$basePathModels}/Dummy.php"
                    ],
                    'request' => [
                        'path' => "{$basePathRequests}/DummyFormRequest.php",
                        'namespace' => 'App\\Http\\Requests',
                        'class' => 'DummyFormRequest'
                    ],
                    'controller' => [
                        'path' => "{$basePathControllers}/DummyController.php",
                        'namespace' => 'App\\Http\\Controllers',
                        'class' => 'DummyController'
                    ],
                    'resource' => [
                        'path' => "{$basePathResources}/DummyResource.php",
                        'namespace' => 'App\\Http\\Resources',
                        'class' => 'DummyResource',
                        'base-class' => 'ResourceObject'
                    ],
                    'factory' => [
                        'path' => "{$basePathFactories}/DummyFactory.php"
                    ],
                    'alias' => [
                        'path' => 'config/jsonapi-alias.php',
                        'content' => [
                            'groups' => [
                                [
                                    'type' => 'dummy-type',
                                    'route' => 'dummy.route',
                                    'model' => 'App\\Models\\Dummy',
                                    'request' => 'App\\Http\\Requests\\DummyFormRequest',
                                    'resource' => 'App\\Http\\Resources\\DummyResource',
                                    'controller' => 'App\\Http\\Controllers\\DummyController'
                                ]
                            ]
                        ]
                    ],
                    'routes' => [
                        'path' => 'routes/api.php',
                        'content' => <<<EOT
                            Route::jsonApiResource(
                                'dummy.route',
                                'DummyController'
                            );
                            EOT
                    ]
                ]
            ],
            'with default namespace' => [
                [
                    'name' => 'Models\Dummy',
                    'type' => 'dummy-type',
                    'route' => 'dummy.route',
                ],
                [
                    'model' => [
                        'path' => "{$basePathModels}/Dummy.php"
                    ],
                    'alias' => [
                        'path' => 'config/jsonapi-alias.php',
                        'content' => [
                            'groups' => [
                                [
                                    'type' => 'dummy-type',
                                    'route' => 'dummy.route',
                                    'model' => 'App\\Models\\Dummy'
                                ]
                            ]
                        ]
                    ],
                    'routes' => [
                        'path' => 'routes/api.php',
                        'content' => <<<EOT
                            Route::jsonApiResource(
                                'dummy.route',
                                null
                            );
                            EOT
                    ]
                ]
            ],
            'with complete default namespace' => [
                [
                    'name' => 'App\Models\Dummy',
                    'type' => 'dummy-type',
                    'route' => 'dummy.route',
                ],
                [
                    'model' => [
                        'path' => "{$basePathModels}/Dummy.php"
                    ]
                ]
            ],
            'with sub namespace' => [
                [
                    'name' => 'Sub\Dummy',
                    'type' => 'dummy-type',
                    'route' => 'dummy.route',
                    '--factory' => true,
                    '--seed' => true,
                    '--jsonapi-controller' => true,
                    '--jsonapi-request' => true,
                    '--jsonapi-resources' => true,
                    '--types' => 'ric',
                    '--relationships' => true
                ],
                [
                    'model' => [
                        'path' => "{$basePathModels}/Sub/Dummy.php"
                    ],
                    'request' => [
                        'path' => "{$basePathRequests}/Sub/DummyFormRequest.php",
                        'namespace' => 'App\\Http\\Requests\\Sub',
                        'class' => 'DummyFormRequest'
                    ],
                    'controller' => [
                        'path' => "{$basePathControllers}/Sub/DummyController.php",
                        'namespace' => 'App\\Http\\Controllers\\Sub',
                        'class' => 'DummyController'
                    ],
                    'resource' => [
                        'path' => "{$basePathResources}/Sub/DummyResourceIdentifierCollection.php",
                        'namespace' => 'App\\Http\\Resources\\Sub',
                        'class' => 'DummyResourceIdentifierCollection',
                        'base-class' => 'ResourceIdentifierCollection'
                    ],
                    'factory' => [
                        'path' => "{$basePathFactories}/Sub/DummyFactory.php"
                    ],
                    'seed' => [
                        'path' => "{$basePathSeeds}/DummySeeder.php"
                    ],
                    'alias' => [
                        'path' => 'config/jsonapi-alias.php',
                        'content' => [
                            'groups' => [
                                [
                                    'type' => 'dummy-type',
                                    'route' => 'dummy.route',
                                    'model' => 'App\\Models\\Sub\\Dummy',
                                    'request' => 'App\\Http\\Requests\\Sub\\DummyFormRequest',
                                    'resource-identifier-collection' => 'App\\Http\\Resources\\Sub\\DummyResourceIdentifierCollection',
                                    'controller' => 'App\\Http\\Controllers\\Sub\\DummyController'
                                ]
                            ]
                        ]
                    ],
                    'routes' => [
                        'path' => 'routes/api.php',
                        'content' => <<<EOT
                            Route::jsonApiResource(
                                'dummy.route',
                                'Sub\\\\DummyController',
                                [
                                    'relationships' => true
                                ]
                            );
                            EOT
                    ]
                ]
            ],
        ];
    }
}
