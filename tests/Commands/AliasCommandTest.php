<?php

namespace VGirol\JsonApi\Tests\Commands;

use VGirol\JsonApi\Exceptions\JsonApiException;

class AliasCommandTest extends CommandTestCase
{
    /**
     * @test
     */
    public function createAliasFileWithMinimalContent()
    {
        $expectedPath = 'config/jsonapi-alias.php';

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type', 'route' => 'dummyRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type',
                    'route' => 'dummyRoute'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);
    }

    /**
     * @test
     */
    public function createAliasFileWithCompleteContent()
    {
        $expectedPath = 'config/jsonapi-alias.php';

        $this->artisan('jsonapi:alias', [
            'type' => 'dummy-type',
            'route' => 'dummyRoute',
            '--api-model' => 'DummyModel',
            '--api-request' => 'DummyFormRequest',
            '--api-resource-ro' => 'DummyResource',
            '--api-controller' => 'DummyController'
        ])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type',
                    'route' => 'dummyRoute',
                    'model' => 'DummyModel',
                    'request' => 'DummyFormRequest',
                    'resource' => 'DummyResource',
                    'controller' => 'DummyController'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);
    }

    /**
     * @test
     */
    public function addGroupToAliasFile()
    {
        $expectedPath = 'config/jsonapi-alias.php';

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type', 'route' => 'dummyRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $this->expectException(JsonApiException::class);
        jsonapiAliases()->getResourceType('secondRoute');

        $this->artisan('jsonapi:alias', ['type' => 'second-type', 'route' => 'secondRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type',
                    'route' => 'dummyRoute'
                ],
                [
                    'type' => 'second-type',
                    'route' => 'secondRoute'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);
    }

    /**
     * @test
     */
    public function updateGroup()
    {
        $expectedPath = 'config/jsonapi-alias.php';

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type', 'route' => 'dummyRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type',
                    'route' => 'dummyRoute'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type2', 'route' => 'dummyRoute'])
             ->expectsOutput('One of these keys is allready present in config file.')
             ->expectsChoice('Do you want to update or cancel ?', 'Update', ['Cancel', 'Update'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type2',
                    'route' => 'dummyRoute'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);
    }

    /**
     * @test
     */
    public function updateCanceled()
    {
        $expectedPath = 'config/jsonapi-alias.php';

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type', 'route' => 'dummyRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Config file "jsonapi-alias.php" successfully updated.');

        $this->assertDiskHas($expectedPath);

        $this->artisan('jsonapi:alias', ['type' => 'dummy-type', 'route' => 'dummyRoute2'])
             ->expectsOutput('One of these keys is allready present in config file.')
             ->expectsChoice('Do you want to update or cancel ?', 'Cancel', ['Cancel', 'Update'])
             ->expectsOutput('Operation canceled.')
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);

        $expectedConfig = [
            'groups' => [
                [
                    'type' => 'dummy-type',
                    'route' => 'dummyRoute'
                ]
            ]
        ];

        $this->assertAliasConfigFileContains($expectedPath, $expectedConfig);
    }
}
