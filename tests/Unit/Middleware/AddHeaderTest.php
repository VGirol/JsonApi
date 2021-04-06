<?php

namespace VGirol\JsonApi\Tests\Unit\Middleware;

use Illuminate\Http\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Middleware\AddResponseHeaders;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;

class AddHeaderTest extends TestCase
{
    use CanCreateRequest;

    public function setUp(): void
    {
        parent::setUp();
        config([
            'logging.channels' => [
                'test' => [
                    'driver' => 'custom',
                    'via' => function () {
                        $monolog = new Logger('test');
                        $monolog->pushHandler(new TestHandler());
                        return $monolog;
                    },
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function responseHasValidContentTypeHeader()
    {
        $mediaType = $this->getMediaType();

        $request = $this->createRequest('/', 'GET', [], [], [], ['Content-Type' => $mediaType]);

        $response = new Response();

        $middleware = new AddResponseHeaders();
        $response = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        $this->assertTrue($response->headers->contains('Content-Type', $mediaType));
    }

    /**
     * @test
     */
    public function responseContentTypeHeaderMustNotHaveBadMediaType()
    {
        $mediaType = $this->getMediaType();

        $request = $this->createRequest('/', 'GET', [], [], [], ['Content-Type' => $mediaType]);

        $response = new Response();
        $response->header('Content-Type', 'application/json');

        $middleware = new AddResponseHeaders();
        $response = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        $this->assertTrue($response->headers->contains('Content-Type', $mediaType));

        // Retrieve the records from the Monolog TestHandler
        $records = app('log')
            ->getHandlers()[0]
            ->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals(
            sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_ALLREADY_SET, $mediaType),
            $records[0]['message']
        );
    }

    /**
     * @test
     */
    public function responseContentTypeHeaderMustNotHaveParameters()
    {
        $mediaType = $this->getMediaType();

        $request = $this->createRequest('/', 'GET', [], [], [], ['Content-Type' => $mediaType]);

        $response = new Response();
        $response->header('Content-Type', "{$mediaType}; param=value");

        $middleware = new AddResponseHeaders();
        $response = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        // Check response status code
        PHPUnit::assertNotNull($response);
        $this->assertTrue($response->headers->contains('Content-Type', $mediaType));

        // Retrieve the records from the Monolog TestHandler
        $records = app('log')
            ->getHandlers()[0]
            ->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals(
            sprintf(Messages::ERROR_CONTENT_TYPE_HEADER_WITHOUT_PARAMETERS, $mediaType),
            $records[0]['message']
        );
    }
}
