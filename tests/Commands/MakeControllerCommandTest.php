<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeControllerCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createControllerFileProvider
     */
    public function createControllerFile($name, $expectedPath, $expectedNamespace, $expectedClass)
    {
        $this->artisan('make:jsonapi:controller', ['name' => $name])
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);
        $this->assertControllerFileContains($expectedPath, $expectedNamespace, $expectedClass);
    }

    public function createControllerFileProvider()
    {
        $basePath = '/app/Http/Controllers';

        return [
            'without namespace' => [
                'Dummy',
                "{$basePath}/Dummy.php",
                'App\\Http\\Controllers',
                'Dummy'
            ],
            'with complete default namespace' => [
                'App\Http\Controllers\Dummy',
                "{$basePath}/Dummy.php",
                'App\\Http\\Controllers',
                'Dummy'
            ],
            'with namespace' => [
                'Sub\Dummy',
                "{$basePath}/Sub/Dummy.php",
                'App\\Http\\Controllers\\Sub',
                'Dummy'
            ],
        ];
    }
}
