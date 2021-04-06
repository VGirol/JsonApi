<?php

namespace VGirol\JsonApi\Tests\Commands;

class MakeModelCommandTest extends CommandTestCase
{
    /**
     * @test
     * @dataProvider createModelFileProvider
     */
    public function createModelFile($name, $expected)
    {
        $this->artisan('make:jsonapi:model', ['name' => $name])
             ->assertExitCode(0);

        $this->assertDiskHas($expected);
    }

    public function createModelFileProvider()
    {
        $basPath = '/app/Models';

        return [
            'without namespace' => [
                'Dummy',
                "{$basPath}/Dummy.php"
            ],
            'with default namespace' => [
                'Models\Dummy',
                "{$basPath}/Dummy.php"
            ],
            'with complete default namespace' => [
                'App\Models\Dummy',
                "{$basPath}/Dummy.php"
            ],
            'with namespace' => [
                'Sub\Dummy',
                "{$basPath}/Sub/Dummy.php"
            ],
        ];
    }
}
