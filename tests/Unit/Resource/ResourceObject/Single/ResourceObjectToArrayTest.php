<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Single;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;

class ResourceObjectToArrayTest extends TestCase
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
        $this->setUpToolsRoutes();
        $this->setUpToolsModels();
    }

    /**
     * @test
     */
    public function exportEmptyResourceObject()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);
        $includes = collect();

        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceObject::make(null, $includes);

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        PHPUnit::assertNull($data);
    }

    /**
     * @test
     */
    public function exportResourceObject()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);
        $includes = collect();

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET');

        // Creates a resource
        $resource = ResourceObject::make($model, $includes);

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertResourceObjectEquals($expected, $data);
    }
}
