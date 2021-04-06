<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Services\SortService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\PhpunitException\SetExceptionsTrait;

class SortServiceTest extends TestCase
{
    use CanCreateRequest;
    use SetExceptionsTrait;
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTools(true);
    }

    /**
     * @test
     */
    public function parseRequest()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'field1,-field2';
        $expected = [ '+field1', '-field2' ];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);

        PHPUnit::assertNull($service->parameters());

        $obj = $service->parseRequest();

        PHPUnit::assertSame($obj, $service);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals($expected, $parameters->toArray());
    }

    /**
     * @test
     */
    public function getQueryParameter()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'field1,-field2';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);

        $expected = [
            $configKey => $value
        ];

        // Has query
        PHPUnit::assertEquals($expected, $service->getQueryParameter());

        // Not has query
        $request = $this->createRequest('/photos', 'GET');
        $service->parseRequest($request, true);
        PHPUnit::assertEmpty($service->getQueryParameter());
    }

    /**
     * @test
     */
    public function allowedSorts()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'field1,-field2';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);
        $service->parseRequest();

        $expected = ['field1', 'field2'];

        PHPUnit::assertEquals($expected, $service->allowedSorts());
    }

    /**
     * @test
     */
    public function queryIsValidThrowsException()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'PHOTO_TITLE,-PHOTO_SIZE';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);
        $service->parseRequest();

        $this->setFailure(JsonApiException::class, 'Parameter "$tableName" can not be null.');

        $service->queryIsValid();
    }

    /**
     * @test
     */
    public function queryIsValid()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'PHOTO_TITLE,-PHOTO_SIZE';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);
        $service->parseRequest();

        PHPUnit::assertTrue($service->queryIsValid('photo'));
    }

    /**
     * @test
     */
    public function queryIsValidFail()
    {
        $configKey = config('query-builder.parameters.sort');
        $value = 'PHOTO_TITLE,-SIZE';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(SortService::class);
        $service->parseRequest();

        PHPUnit::assertFalse($service->queryIsValid('photo'));
    }
}
