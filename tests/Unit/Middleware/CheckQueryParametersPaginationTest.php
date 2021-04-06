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

class CheckQueryParametersPaginationTest extends TestCase
{
    use CanCreateRequest;
    use UsesTools {
        setUpTools as setUpTestTools;
    }

    private $pagination_parameter;
    private $number_parameter;
    private $size_parameter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->number_parameter = config('json-api-paginate.number_parameter');
        $this->size_parameter = config('json-api-paginate.size_parameter');
        $this->pagination_parameter = config('json-api-paginate.size_parameter');

        $this->setUpTestTools(true);
    }

    /**
     * @test
     */
    public function requestHasValidPaginationParameter()
    {
        // Sets config
        config(['jsonapi.pagination.allowed' => true]);

        $request = $this->createRequest(
            route('photos.index', ['page' => ['number' => 2, 'size' => 25]]),
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
     * Server does not support pagination
     * Returns 400
     *
     * @test
     */
    public function serverDoesNotSupportPagination()
    {
        // Sets config
        config(['jsonapi.pagination.allowed' => false]);

        $request = $this->createRequest(
            route('photos.index', ['page' => ['number' => 2, 'size' => 25]]),
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
            Messages::ERROR_QUERY_PARAMETER_PAGINATION_NOT_ALLOWED_BY_SERVER,
            $errors[0][Members::ERROR_DETAILS]
        );
    }

    /**
     * Pagination is not allowed for this request
     * Returns 400
     *
     * @test
     */
    public function paginationIsNotAllowedForThisRequest()
    {
        // Sets config
        config(['jsonapi.pagination.allowed' => true]);

        $request = $this->createRequest(
            route('photos.destroy', ['id' => 666, 'page' => ['number' => 2, 'size' => 25]]),
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
            Messages::ERROR_QUERY_PARAMETER_PAGINATION_NOT_ALLOWED_FOR_ROUTE,
            $errors[0][Members::ERROR_DETAILS]
        );
    }
}
