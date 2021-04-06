<?php

namespace VGirol\JsonApi\Tests\Unit\Resource;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\CanCreateLink;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;

class CanCreateLinkTest extends TestCase
{
    use CanCreateRequest;
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // $this->setUpToolsAliases(true);
        // $this->setUpToolsRoutes();
        // $this->setUpToolsModels();
    }

    /**
     * @test
     * @dataProvider getDocumentSelfLinkProvider
     */
    public function getDocumentSelfLink($baseRoute, $queries)
    {
        $route = $baseRoute;
        $query = array_map(
            function ($key, $value) {
                return $key . '=' . $value;
            },
            array_keys($queries),
            $queries
        );
        if (!empty($query)) {
            sort($query);
            $route .= '?' . implode('&', $query);
        }

        // Creates a request
        $request = $this->createRequest(
            $route,
            'GET'
        );

        // Creates a resource
        /**
         * @var CanCreateLink $mock
         */
        $mock = $this->getMockForTrait(CanCreateLink::class);

        // Creates the expected objects
        $expected = $baseRoute;
        $query = array_map(
            function ($key, $value) {
                return $key . '=' . rawurlencode($value);
            },
            array_keys($queries),
            $queries
        );
        if (!empty($query)) {
            sort($query);
            $expected .= '?' . implode('&', $query);
        }

        // Exports model
        $result = $mock->getDocumentSelfLink($request);

        // Assertions
        PHPUnit::assertIsString($result);
        PHPUnit::assertStringEndsWith($expected, $result);
    }

    public function getDocumentSelfLinkProvider()
    {
        return [
            'no query string' => [
                'dummyRoute',
                []
            ],
            'query string' => [
                'dummyRoute',
                ['param' => 'value', 'key' => 'value']
            ],
            'query string with special characters' => [
                'dummyRoute',
                ['param' => '@value', 'key' => 'value%']
            ],
        ];
    }
}
