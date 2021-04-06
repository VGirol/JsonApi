<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeResourceCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createResourceFileProvider
     */
    public function createResourceFile($args, $expectedPath, $expectedNamespace, $expectedClass, $expectedBaseClass)
    {
        $this->artisan('make:jsonapi:resource', $args)
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);
        $this->assertResourceFileContains($expectedPath, $expectedNamespace, $expectedClass, $expectedBaseClass);
    }

    public function createResourceFileProvider()
    {
        $basePath = '/app/Http/Resources';

        return [
            'without namespace' => [
                [
                    'name' => 'Dummy',
                    '--ro' => true
                ],
                "{$basePath}/Dummy.php",
                'App\\Http\\Resources',
                'Dummy',
                'ResourceObject'
            ],
            'with complete default namespace' => [
                [
                    'name' => 'App\Http\Resources\Dummy',
                    '--ri' => true
                ],
                "{$basePath}/Dummy.php",
                'App\\Http\\Resources',
                'Dummy',
                'ResourceIdentifier'
            ],
            'with namespace' => [
                [
                    'name' => 'Sub\Dummy',
                    '--ri' => true,
                    '--collection' => true
                ],
                "{$basePath}/Sub/Dummy.php",
                'App\\Http\\Resources\\Sub',
                'Dummy',
                'ResourceIdentifierCollection'
            ],
        ];
    }
}
