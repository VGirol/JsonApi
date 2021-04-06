<?php

namespace VGirol\JsonApi\Tests\Unit\Middleware;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Middleware\CheckRequestHeaders;
use VGirol\JsonApi\Services\ResponseService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;

class CheckHeaderTest extends TestCase
{
    use CanCreateRequest;

    /**
     * Should return 400
     *
     * @test
     */
    public function requestHasNoContentTypeHeader()
    {
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Accept' => null]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function ($request) {
            return null;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(400, $response->getStatusCode());
    }

    /**
     * Should return 400
     *
     * @test
     */
    public function requestHasContentTypeHeaderWithBadMediaType()
    {
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => 'application/json', 'Accept' => null]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function () {
            return null;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(400, $response->getStatusCode());
    }

    /**
     * Should return 415
     *
     * @test
     */
    public function requestHasContentTypeHeaderWithMediaTypeParameter()
    {
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => "{$this->getMediaType()}; charset=utf-8", 'Accept' => null]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function () {
            return null;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(415, $response->getStatusCode());
    }


    /**
     * @test
     */
    public function requestHasNoAcceptHeader()
    {
        $mediaType = $this->getMediaType();
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => $mediaType]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function ($req) {
            PHPUnit::assertTrue(true);
            return null;
        });

        PHPUnit::assertNull($response);
    }

    /**
     * @test
     */
    public function requestHasAcceptHeaderWithoutJsonapiMediaType()
    {
        $mediaType = $this->getMediaType();
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => $mediaType, 'Accept' => ['application/json']]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function ($req) {
            PHPUnit::assertTrue(true);
            return null;
        });

        PHPUnit::assertNull($response);
    }

    /**
     * Should return 406
     *
     * @test
     */
    public function requestHasNotValidAcceptHeader()
    {
        $mediaType = $this->getMediaType();

        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'Content-Type' => $mediaType,
                'Accept' => "{$mediaType}; param=value, application/json, {$mediaType}; charset=utf-8"
            ]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function ($req) {
            return null;
        });

        PHPUnit::assertNotNull($response);
        PHPUnit::assertEquals(406, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function requestHasValidContentTypeAndAcceptHeader()
    {
        $mediaType = $this->getMediaType();
        $request = $this->createRequest(
            '/',
            'GET',
            [],
            [],
            [],
            ['Content-Type' => $mediaType, 'Accept' => ["{$mediaType}"]]
        );

        $middleware = new CheckRequestHeaders(new ResponseService());
        $response = $middleware->handle($request, function ($req) {
            PHPUnit::assertTrue(true);
            return null;
        });

        PHPUnit::assertNull($response);
    }
}
