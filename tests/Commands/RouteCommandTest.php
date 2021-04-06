<?php

namespace VGirol\JsonApi\Tests\Commands;

class RouteCommandTest extends CommandTestCase
{
    /**
     * @test
     */
    public function createRouteFileWithMinimalContent()
    {
        $expectedPath = 'routes/api.php';

        $this->artisan('jsonapi:route', ['route' => 'dummyRoute'])
             ->assertExitCode(0)
             ->expectsOutput('Routes file "' . $expectedPath . '" successfully updated.');

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            null
        );
        EOT;

        $this->assertRoutesFileContains($expectedPath, $expectedContent);
    }

    /**
     * @test
     */
    public function createRouteFileWithCompleteContent()
    {
        $expectedPath = 'routes/api.php';

        $this->artisan('jsonapi:route', [
            'route' => 'dummyRoute',
            '--controller' => 'DummyController',
            '--relationships' => true
        ])
             ->assertExitCode(0)
             ->expectsOutput('Routes file "' . $expectedPath . '" successfully updated.');

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            'DummyController',
            [
                'relationships' => true
            ]
        );

        EOT;

        $this->assertRoutesFileContains($expectedPath, $expectedContent);
    }

    /**
     * @test
     */
    public function updateRoute()
    {
        $expectedPath = 'routes/api.php';

        $this->artisan('jsonapi:route', ['route' => 'dummyRoute'])
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);

        $this->artisan('jsonapi:route', [
            'route' => 'dummyRoute',
            '--controller' => 'DummyController'
        ])
             ->expectsOutput('A route with the same name allready exists.')
             ->expectsChoice('Do you want to update or cancel ?', 'Update', ['Cancel', 'Update'])
             ->expectsOutput('Routes file "' . $expectedPath . '" successfully updated.')
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);

        $content = $this->disk->get($expectedPath);

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            null
        );
        EOT;

        $this->assertStringNotContainsString($expectedContent, $content);

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            'DummyController'
        );
        EOT;

        $this->assertStringContainsString($expectedContent, $content);
    }

    /**
     * @test
     */
    public function updateCanceled()
    {
        $expectedPath = 'routes/api.php';

        $this->artisan('jsonapi:route', ['route' => 'dummyRoute'])
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);

        $this->artisan('jsonapi:route', [
            'route' => 'dummyRoute',
            '--controller' => 'DummyController'
        ])
             ->expectsOutput('A route with the same name allready exists.')
             ->expectsChoice('Do you want to update or cancel ?', 'Cancel', ['Cancel', 'Update'])
             ->expectsOutput('Operation canceled.')
             ->assertExitCode(0);

        $this->assertDiskHas($expectedPath);

        $content = $this->disk->get($expectedPath);

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            null
        );
        EOT;

        $this->assertStringContainsString($expectedContent, $content);

        $expectedContent = <<<EOT
        Route::jsonApiResource(
            'dummyRoute',
            'DummyController'
        );
        EOT;

        $this->assertStringNotContainsString($expectedContent, $content);
    }
}
