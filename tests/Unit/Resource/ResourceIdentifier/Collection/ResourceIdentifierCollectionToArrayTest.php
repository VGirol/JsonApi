<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceIdentifier\Collection;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;

class ResourceIdentifierCollectionToArrayTest extends TestCase
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
        $this->setUpToolsModels();
    }

    /**
     * @test
     */
    public function exportEmptyCollection()
    {
        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceIdentifierCollection::make(collect([]));

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        PHPUnit::assertEquals([], $data);
    }

    /**
     * @test
     */
    public function exportCollection()
    {
        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make();

        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceIdentifierCollection::make($collection);

        // Creates expected result
        $expected = (new HelperFactory())->riCollection($collection, 'photo')
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceLinkage($data, $strict);
        Assert::assertResourceIdentifierCollectionEquals($expected, $data);
    }
}
