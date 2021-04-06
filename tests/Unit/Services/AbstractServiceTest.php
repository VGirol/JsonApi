<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\AbstractService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;

class AbstractServiceTest extends TestCase
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
    public function parseRequestOnceWithDefaultParameters()
    {
        $collection = new Collection([]);
        $request = $this->createRequest('/', 'GET');
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($req) use ($request, $collection) {
                    PHPUnit::assertSame($req, $request);
                    return $collection;
                }));

        PHPUnit::assertNull($service->parameters());

        $obj = $service->parseRequest();

        PHPUnit::assertSame($obj, $service);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertSame($collection, $parameters);
    }

    /**
     * @test
     */
    public function parseRequestTwiceWithDefaultParametersUseCache()
    {
        $value = 'test';
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        PHPUnit::assertNull($service->parameters());

        $service->parseRequest();

        $first = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $first);
        PHPUnit::assertEquals([$value], $first->toArray());

        $service->parseRequest($request);

        PHPUnit::assertSame($first, $service->parameters());
    }

    /**
     * @test
     */
    public function parseRequestTwiceForce()
    {
        $value = 'test';
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->exactly(2))
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        PHPUnit::assertNull($service->parameters());

        $service->parseRequest($request);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals([$value], $parameters->toArray());

        $service->parseRequest($request, true);
    }

    /**
     * @test
     */
    public function hasQuery()
    {
        $value = 'test';
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        PHPUnit::assertNull($service->parameters());

        $result = $service->hasQuery($request);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals([$value], $parameters->toArray());
        PHPUnit::assertTrue($result);
    }

    /**
     * @test
     */
    public function notHasQuery()
    {
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET');

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        PHPUnit::assertNull($service->parameters());

        $result = $service->hasQuery($request);

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEmpty($parameters->toArray());
        PHPUnit::assertFalse($result);
    }

    /**
     * @test
     * @dataProvider getParameterValueProvider
     */
    public function getParameterValue($value, $key, $expected)
    {
        $queryName = 'query1';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        PHPUnit::assertNull($service->value($queryName));

        $service->parseRequest($request);

        PHPUnit::assertEquals($expected, $service->value($key));
        PHPUnit::assertEquals('default', $service->value('badKey', 'default'));
    }

    public function getParameterValueProvider()
    {
        return [
            'string' => [
                'test',
                0,
                'test'
            ],
            'array' => [
                [ 'attr1' => 'value1', 'attr2' => 'value2' ],
                'attr2',
                'value2'
            ]
        ];
    }

    /**
     * @test
     */
    public function getQueryParameter()
    {
        $queryName = 'query';
        $value = 'test';
        $configKey = 'configKey';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters', 'getConfigKey']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        $service->expects($this->once())
                ->method('getConfigKey')
                ->will($this->returnValue($configKey));

        PHPUnit::assertNull($service->parameters());

        $result = $service->getQueryParameter();

        $expected = [
            $configKey => [$value]
        ];

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals([$value], $parameters->toArray());
        PHPUnit::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getQueryParameterWhenNoQueryString()
    {
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET');
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters', 'getConfigKey']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        $service->expects($this->never())
                ->method('getConfigKey');

        PHPUnit::assertNull($service->parameters());

        $result = $service->getQueryParameter();

        $parameters = $service->parameters();

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEmpty($parameters->toArray());
        PHPUnit::assertIsArray($result);
        PHPUnit::assertEmpty($result);
    }

    /**
     * @test
     */
    public function implode()
    {
        $value = ['attr1' => 'value1', 'attr2' => 'value2'];
        $queryName = 'query';
        $request = $this->createRequest('/', 'GET', [$queryName => $value]);
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['parseParameters']
        );

        $service->expects($this->once())
                ->method('parseParameters')
                ->will($this->returnCallback(function ($request) use ($queryName) {
                    return Collection::make($request->query($queryName, null));
                }));

        $service->parseRequest();

        PHPUnit::assertEquals('value1, value2', $service->implode());
        PHPUnit::assertEquals('value1 - value2', $service->implode(' - '));
    }

    /**
     * @test
     */
    public function allowedByServer()
    {
        $configKey = 'configKey';
        $request = $this->createRequest('/', 'GET');
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['getConfigKey']
        );

        $service->expects($this->exactly(2))
                ->method('getConfigKey')
                ->will($this->returnValue($configKey));

        config()->set("jsonapi.{$configKey}.allowed", true);
        PHPUnit::assertTrue($service->allowedByServer());

        config()->set("jsonapi.{$configKey}.allowed", false);
        PHPUnit::assertFalse($service->allowedByServer());
    }

    /**
     * @test
     */
    public function allowedForRoute()
    {
        $configKey = 'configKey';
        $request = $this->createRequest('/photos', 'GET');
        $this->swap('request', $request);

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['getConfigKey']
        );

        $service->expects($this->exactly(2))
                ->method('getConfigKey')
                ->will($this->returnValue($configKey));

        config()->set("jsonapi.{$configKey}.routes", ['*.index', '*.show', '*.store', '*.update']);
        PHPUnit::assertTrue($service->allowedForRoute());

        config()->set("jsonapi.{$configKey}.routes", ['*.store', '*.update']);
        PHPUnit::assertFalse($service->allowedForRoute());
    }

    /**
     * @test
     */
    public function allowed()
    {
        $configKey = 'configKey';
        $request = $this->createRequest('/photos', 'GET');

        $service = $this->getMockForAbstractClass(
            AbstractService::class,
            [],
            '',
            true,
            true,
            true,
            ['getConfigKey']
        );

        $service->expects($this->atLeast(1))
                ->method('getConfigKey')
                ->will($this->returnValue($configKey));

        config()->set("jsonapi.{$configKey}.allowed", true);
        config()->set("jsonapi.{$configKey}.routes", ['*.index', '*.show', '*.store', '*.update']);
        PHPUnit::assertTrue($service->allowed($request));

        config()->set("jsonapi.{$configKey}.allowed", true);
        config()->set("jsonapi.{$configKey}.routes", ['*.store', '*.update']);
        PHPUnit::assertFalse($service->allowed($request));

        config()->set("jsonapi.{$configKey}.allowed", false);
        config()->set("jsonapi.{$configKey}.routes", ['*.index', '*.show', '*.store', '*.update']);
        PHPUnit::assertFalse($service->allowed($request));

        config()->set("jsonapi.{$configKey}.allowed", false);
        config()->set("jsonapi.{$configKey}.routes", ['*.store', '*.update']);
        PHPUnit::assertFalse($service->allowed($request));
    }
}
