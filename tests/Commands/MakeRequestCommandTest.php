<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeRequestCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createRequestFileProvider
     */
    public function createRequestFile($name, $expectedPath, $expectedNamespace, $expectedClass)
    {
        $this->artisan('make:jsonapi:request', ['name' => $name])
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);
        $this->assertFormRequestFileContains($expectedPath, $expectedNamespace, $expectedClass);
    }

    public function createRequestFileProvider()
    {
        $basePath = '/app/Http/Requests';

        return [
            'without namespace' => [
                'Dummy',
                "{$basePath}/Dummy.php",
                'App\\Http\\Requests',
                'Dummy'
            ],
            'with complete default namespace' => [
                'App\Http\Requests\Dummy',
                "{$basePath}/Dummy.php",
                'App\\Http\\Requests',
                'Dummy'
            ],
            'with namespace' => [
                'Sub\Dummy',
                "{$basePath}/Sub/Dummy.php",
                'App\\Http\\Requests\\Sub',
                'Dummy'
            ],
        ];
    }
}
