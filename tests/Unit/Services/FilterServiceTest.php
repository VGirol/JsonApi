<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\FilterService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;

class FilterServiceTest extends TestCase
{
    use CanCreateRequest;

    /**
     * @test
     */
    public function parseRequest()
    {
        $configKey = config('query-builder.parameters.filter');
        $value = ['field1' => 'value1'];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FilterService::class);

        PHPUnit::assertNull($service->parameters());

        $obj = $service->parseRequest();

        PHPUnit::assertSame($obj, $service);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals($value, $parameters->toArray());
    }

    /**
     * @test
     */
    public function allowedFilters()
    {
        $configKey = config('query-builder.parameters.filter');
        $value = ['field1' => 'value1', 'field2' => 'value2'];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FilterService::class);
        $service->parseRequest();

        $expected = [ 'field1', 'field2'];

        PHPUnit::assertEquals($expected, $service->allowedFilters());
    }

    /**
     * @test
     */
    public function getQueryParameter()
    {
        $configKey = config('query-builder.parameters.filter');
        $value = ['field1' => 'value1', 'field2' => 'value2'];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FilterService::class);

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
    public function queryIsValid()
    {
        $service = resolve(FilterService::class);

        PHPUnit::assertTrue($service->queryIsValid());
    }
}
