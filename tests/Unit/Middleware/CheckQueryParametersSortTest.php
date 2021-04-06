<?php

namespace VGirol\JsonApi\Tests\Unit\Middleware;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Middleware\CheckQueryParameters;
use VGirol\JsonApi\Services\ResponseService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class CheckQueryParametersSortTest extends TestCase
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

        $this->setUpToolsRoutes();
    }

    /**
     * @test
     */
    public function requestHasValidSortParameter()
    {
        // Sets config
        config(['jsonapi.sort.allowed' => true]);

        $request = $this->createRequest(
            route('photos.index', ['sort' => 'PHOTO_DATE']),
            'GET'
        );

        $middleware = new CheckQueryParameters(new ResponseService());
        $response = $middleware->handle($request, function ($req) {
            PHPUnit::assertTrue(true);
            return null;
        });

        PHPUnit::assertNull($response);
    }

    /**
     * Server does not support sort
     * Returns 400
     *
     * @test
     */
    public function serverDoesNotSupportSort()
    {
        // Sets config
        config(['jsonapi.sort.allowed' => false]);

        $request = $this->createRequest(
            route('photos.index', ['sort' => 'PHOTO_DATE']),
            'GET'
        );

        $middleware = new CheckQueryParameters(new ResponseService());
        $response = $middleware->handle($request, function () {
            return null;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(400, $response->getStatusCode());

        $json = $response->getData(true);
        Assert::assertHasErrors($json);
        $errors = $json[Members::ERRORS];
        PHPUnit::assertEquals(1, count($errors));
        PHPUnit::assertEquals(
            Messages::ERROR_QUERY_PARAMETER_SORT_NOT_ALLOWED_BY_SERVER,
            $errors[0][Members::ERROR_DETAILS]
        );
    }

    /**
     * Sort is not allowed for this request
     * Returns 400
     *
     * @test
     */
    public function sortIsNotAllowedForThisRequest()
    {
        // Sets config
        config(['jsonapi.sort.allowed' => true]);

        $request = $this->createRequest(
            route('photos.destroy', ['id' => 666, 'sort' => 'PHOTO_DATE']),
            'DELETE'
        );

        $middleware = new CheckQueryParameters(new ResponseService());
        $response = $middleware->handle($request, function () {
            return null;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(400, $response->getStatusCode());

        $json = $response->getData(true);
        Assert::assertHasErrors($json);
        $errors = $json[Members::ERRORS];
        PHPUnit::assertEquals(1, count($errors));
        PHPUnit::assertEquals(
            Messages::ERROR_QUERY_PARAMETER_SORT_NOT_ALLOWED_FOR_ROUTE,
            $errors[0][Members::ERROR_DETAILS]
        );
    }
}
