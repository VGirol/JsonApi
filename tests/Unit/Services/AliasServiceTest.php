<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Resources\ResourceIdentifier;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Resources\ResourceObjectCollection;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\DummyModel;
use VGirol\PhpunitException\SetExceptionsTrait;

class AliasServiceTest extends TestCase
{
    use SetExceptionsTrait;
    use CanCreateRequest;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $groups = [
            $this->getDummyConfig(),
            $this->getRelatedConfig()
        ];

        config()->set('jsonapi-alias.groups', $groups);

        $this->buildRoutes($groups);
    }

    private function getDummyConfig()
    {
        return [
            'type' => 'dummyType',
            'route' => 'dummyRoute',
            'model' => DummyModel::class,
            'request' => 'DummyFormRequest',
            'resource-ro' => 'DummyResource',
            'resource-ri' => 'DummyResourceIdentifier',
            'resource-roc' => 'DummyResourceCollection',
            'resource-ric' => 'DummyResourceIdentifierCollection',
            'relationships' => [
                'aliasType' => 'relatedType'
            ]
        ];
    }

    private function getRelatedConfig()
    {
        return [
            'type' => 'relatedType',
            'route' => 'relatedRoute',
            'model' => 'RelatedModel',
            'request' => 'RelatedFormRequest'
        ];
    }

    /**
     * @test
     */
    public function initAndGet()
    {
        $dummy = $this->getDummyConfig();
        $related = $this->getRelatedConfig();

        $service = resolve(AliasesService::class);

        PHPUnit::assertEquals($dummy['model'], $service->getModelClassName('dummyRoute'));
        PHPUnit::assertEquals($dummy['resource-ro'], $service->getResourceClassName('dummyRoute'));
        PHPUnit::assertEquals($dummy['resource-ri'], $service->getResourceIdentifierClassName('dummyRoute'));
        PHPUnit::assertEquals($dummy['resource-roc'], $service->getResourceCollectionClassName('dummyRoute'));
        PHPUnit::assertEquals(
            $dummy['resource-ric'],
            $service->getResourceIdentifierCollectionClassName('dummyRoute')
        );
        PHPUnit::assertEquals($dummy['request'], $service->getFormRequestClassName('dummyRoute'));
        PHPUnit::assertEquals($dummy['type'], $service->getResourceType('dummyRoute'));
        PHPUnit::assertEquals($dummy['route'], $service->getResourceRoute('dummyType'));

        PHPUnit::assertEquals($related['model'], $service->getModelClassName('aliasType'));
    }

    /**
     * @test
     */
    public function initAndGetDefaultValues()
    {
        $related = $this->getRelatedConfig();

        $service = resolve(AliasesService::class);

        PHPUnit::assertEquals($related['model'], $service->getModelClassName('relatedRoute'));
        PHPUnit::assertEquals(ResourceObject::class, $service->getResourceClassName('relatedRoute'));
        PHPUnit::assertEquals(ResourceIdentifier::class, $service->getResourceIdentifierClassName('relatedRoute'));
        PHPUnit::assertEquals(
            ResourceObjectCollection::class,
            $service->getResourceCollectionClassName('relatedRoute')
        );
        PHPUnit::assertEquals(
            ResourceIdentifierCollection::class,
            $service->getResourceIdentifierCollectionClassName('relatedRoute')
        );
        PHPUnit::assertEquals($related['request'], $service->getFormRequestClassName('relatedRoute'));
        PHPUnit::assertEquals($related['type'], $service->getResourceType('relatedRoute'));
        PHPUnit::assertEquals($related['route'], $service->getResourceRoute('relatedType'));
    }

    /**
     * @test
     */
    public function initAndGetThrowsException()
    {
        $config = [
            [
                'type' => 'dummyType',
                'route' => 'dummyRoute',
                'model' => 'DummyModel'
            ]
        ];
        config()->set('jsonapi-alias.groups', $config);

        $service = resolve(AliasesService::class);
        $service->init();

        $this->setFailure(
            JsonApiException::class,
            sprintf(AliasesService::ERROR_PATH_DOES_NOT_EXIST, 'dummyType', 'request')
        );

        $service->getFormRequestClassName('dummyType');
    }

    /**
     * @test
     */
    public function referenceNotValidThrowsException()
    {
        config()->set('jsonapi-alias.groups', []);

        $service = resolve(AliasesService::class);
        $service->init();

        $this->setFailure(
            JsonApiException::class,
            sprintf(AliasesService::ERROR_REF_NOT_VALID, 'badType')
        );

        $service->getFormRequestClassName('badType');
    }

    /**
     * @test
     */
    public function referenceIsModel()
    {
        $dummy = $this->getDummyConfig();

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($dummy['request'], $service->getFormRequestClassName(new DummyModel()));
    }

    /**
     * @test
     */
    public function referenceIsNull()
    {
        $dummy = $this->getDummyConfig();

        $request = $this->createRequest('/' . $dummy['route'], 'GET');
        $this->swap('request', $request);

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($dummy['request'], $service->getFormRequestClassName());
    }

    /**
     * @test
     */
    public function referenceIsRequest()
    {
        $dummy = $this->getDummyConfig();
        $related = $this->getRelatedConfig();

        $request = $this->createRequest('/' . $dummy['route'] . '/1/' . $related['route'], 'POST');

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($related['model'], $service->getModelClassName($request));
        PHPUnit::assertEquals(ResourceObject::class, $service->getResourceClassName($request));
        PHPUnit::assertEquals(ResourceIdentifier::class, $service->getResourceIdentifierClassName($request));
        PHPUnit::assertEquals($related['request'], $service->getFormRequestClassName($request));
        PHPUnit::assertEquals(ResourceObjectCollection::class, $service->getResourceCollectionClassName($request));
        PHPUnit::assertEquals(
            ResourceIdentifierCollection::class,
            $service->getResourceIdentifierCollectionClassName($request)
        );
        PHPUnit::assertEquals($related['type'], $service->getResourceType($request));
        PHPUnit::assertEquals($related['route'], $service->getResourceRoute($request));
        PHPUnit::assertEquals($dummy['route'], $service->getParentRoute($request));

        PHPUnit::assertEquals($dummy['model'], $service->getParentClassName($request));
    }

    /**
     * @test
     */
    public function getKeyNameThrowsException()
    {
        $obj = new class () {
            // nothing
        };

        $service = resolve(AliasesService::class);
        $service->init();

        $this->setFailure(
            JsonApiException::class,
            AliasesService::ERROR_NO_ROUTE
        );

        $service->getFormRequestClassName($obj);
    }

    /**
     * @test
     */
    public function getFormRequestForRelated()
    {
        $dummy = $this->getDummyConfig();
        $related = $this->getRelatedConfig();

        $request = $this->createRequest('/' . $dummy['route'] . '/1/' . $related['route'], 'GET');

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($related['request'], $service->getFormRequestClassName($request));
    }

    /**
     * @test
     */
    public function getFormRequestForRelationship()
    {
        $dummy = $this->getDummyConfig();
        $related = $this->getRelatedConfig();

        $request = $this->createRequest('/' . $dummy['route'] . '/1/relationships/' . $related['route'], 'GET');

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($related['request'], $service->getFormRequestClassName($request));
    }

    /**
     * @test
     */
    public function getParentForRelated()
    {
        $dummy = $this->getDummyConfig();
        $related = $this->getRelatedConfig();

        $request = $this->createRequest('/' . $dummy['route'] . '/1/' . $related['route'], 'GET');

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals($dummy['model'], $service->getParentClassName($request));
    }

    /**
     * @test
     */
    public function getModelKeyName()
    {
        $dummy = $this->getDummyConfig();

        $request = $this->createRequest('/' . $dummy['route'], 'GET');

        $service = resolve(AliasesService::class);
        $service->init();

        PHPUnit::assertEquals('DUMMY_ID', $service->getModelKeyName($request));
    }
}
