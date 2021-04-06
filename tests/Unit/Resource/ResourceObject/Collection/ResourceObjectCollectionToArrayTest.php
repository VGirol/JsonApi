<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Collection;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceObjectCollection;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceObjectCollectionToArrayTest extends TestCase
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
    public function exportEmptyResourceObjectCollection()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);

        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceObjectCollection::make(collect(), jsonapiInclude($request)->parameters());

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        PHPUnit::assertIsArray($data);
        PHPUnit::assertEmpty($data);
    }

    /**
     * @test
     */
    public function exportResourceObjectCollection()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make();

        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) {
                $item->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]));
            })
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObjectCollection($data, $strict);
        Assert::assertResourceCollectionEquals($expected, $data);
    }
}
