<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeResourcesCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createResourcesFileProvider
     */
    public function createResourcesFile($args, $expectedFiles)
    {
        $this->artisan('make:jsonapi:resources', $args)
             ->assertExitCode(0);

        foreach ($expectedFiles as $expectedFile) {
            $this->assertDiskHas($expectedFile['path']);
            $this->assertResourceFileContains($expectedFile['path'], $expectedFile['namespace'], $expectedFile['class'], $expectedFile['baseClass']);
        }
    }

    public function createResourcesFileProvider()
    {
        $basePath = '/app/Http/Resources';

        return [
            'without namespace' => [
                [
                    'name' => 'Dummy',
                    '--types' => 'ro'
                ],
                [
                    [
                        'path' => "{$basePath}/DummyResource.php",
                        'namespace' => 'App\\Http\\Resources',
                        'class' => 'DummyResource',
                        'baseClass' => 'ResourceObject'
                    ]
                ]
            ],
            'with complete default namespace' => [
                [
                    'name' => 'App\Http\Resources\Dummy',
                    '--types' => 'ri,ric'
                ],
                [
                    [
                        'path' => "{$basePath}/DummyResourceIdentifier.php",
                        'namespace' => 'App\\Http\\Resources',
                        'class' => 'DummyResourceIdentifier',
                        'baseClass' => 'ResourceIdentifier'
                    ],
                    [
                        'path' => "{$basePath}/DummyResourceIdentifierCollection.php",
                        'namespace' => 'App\\Http\\Resources',
                        'class' => 'DummyResourceIdentifierCollection',
                        'baseClass' => 'ResourceIdentifierCollection'
                    ]
                ]
            ],
            'with namespace' => [
                [
                    'name' => 'Sub\Dummy'
                ],
                [
                    [
                        'path' => "{$basePath}/Sub/DummyResource.php",
                        'namespace' => 'App\\Http\\Resources\\Sub',
                        'class' => 'DummyResource',
                        'baseClass' => 'ResourceObject'
                    ],
                    [
                        'path' => "{$basePath}/Sub/DummyResourceCollection.php",
                        'namespace' => 'App\\Http\\Resources\\Sub',
                        'class' => 'DummyResourceCollection',
                        'baseClass' => 'ResourceObjectCollection'
                    ],
                    [
                        'path' => "{$basePath}/Sub/DummyResourceIdentifier.php",
                        'namespace' => 'App\\Http\\Resources\\Sub',
                        'class' => 'DummyResourceIdentifier',
                        'baseClass' => 'ResourceIdentifier'
                    ],
                    [
                        'path' => "{$basePath}/Sub/DummyResourceIdentifierCollection.php",
                        'namespace' => 'App\\Http\\Resources\\Sub',
                        'class' => 'DummyResourceIdentifierCollection',
                        'baseClass' => 'ResourceIdentifierCollection'
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function badTypesOption()
    {
        $args = [
            'name' => 'App\Http\Resources\Dummy',
            '--types' => 'ro,bad,types'
        ];
        $this->artisan('make:jsonapi:resources', $args)
             ->expectsOutput('The following types are not allowed : "bad", "types".')
             ->assertExitCode(1);
    }
}
