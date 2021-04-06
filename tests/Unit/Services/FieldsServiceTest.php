<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\FieldsService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;

class FieldsServiceTest extends TestCase
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

        $this->setUpToolsAliases(true);
        $this->setUpToolsDB();
    }

    /**
     * @test
     */
    public function parseRequest()
    {
        $configKey = config('query-builder.parameters.fields');
        $value = 'field1,field2';
        $expected = [
            [ 'field1', 'field2' ]
        ];
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FieldsService::class);

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
        $configKey = config('query-builder.parameters.fields');
        $value = 'field1,field2';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FieldsService::class);

        $expected = [
            $configKey => [$value]
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
    public function allowedFields()
    {
        $configKey = config('query-builder.parameters.fields');
        $value = 'field1,field2';
        $request = $this->createRequest('/photos', 'GET', [$configKey => $value]);
        $this->swap('request', $request);

        $service = resolve(FieldsService::class);
        $service->parseRequest();

        $expected = [
            'photo_id',
            'photo_title',
            'photo_size',
            'photo_date'
        ];

        PHPUnit::assertEquals($expected, $service->allowedFields('photo'));
    }

    /**
     * @test
     */
    public function queryIsValid()
    {
        $service = resolve(FieldsService::class);

        PHPUnit::assertTrue($service->queryIsValid());
    }
}
