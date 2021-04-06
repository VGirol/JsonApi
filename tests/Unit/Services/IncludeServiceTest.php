<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\IncludeService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;

class IncludeServiceTest extends TestCase
{
    use CanCreateRequest;

    /**
     * @test
     */
    public function parseRequest()
    {
        $configKey = config('query-builder.parameters.include');
        $value = 'model1,model2.model3';
        $expected = [
            'model1' => [],
            'model2' => [
                'model3' => []
            ]
        ];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(IncludeService::class);

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
        $configKey = config('query-builder.parameters.include');
        $value = 'model1,model2.model3';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(IncludeService::class);

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
    public function allowedIncludes()
    {
        $configKey = config('query-builder.parameters.include');
        $value = 'model1,model2.model3';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(IncludeService::class);
        $service->parseRequest();

        $expected = ['model1', 'model2.model3'];

        PHPUnit::assertEquals($expected, $service->allowedIncludes());
    }

    /**
     * @test
     */
    public function queryIsValid()
    {
        $service = resolve(IncludeService::class);

        PHPUnit::assertTrue($service->queryIsValid());
    }
}
