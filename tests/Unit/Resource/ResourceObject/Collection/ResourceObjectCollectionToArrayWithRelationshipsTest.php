<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Collection;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceObjectCollection;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceObjectCollectionToArrayWithRelationshipsTest extends TestCase
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
    public function exportCollectionWithEmptyToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        $include = 'price';

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make()
            ->each(function ($item) use ($include) {
                $related = null;
                $item->setRelation($include, $related);
            });

        // Creates a request
        $request = $this->createRequest('photos', 'GET', ['include' => $include]);

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($include) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->loadRelationship($include, 'price');
            })
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObjectCollection($data, $strict);
        Assert::assertResourceCollectionEquals($expected, $data);
    }

    /**
     * @test
     */
    public function exportCollectionWithToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        $include = 'price';

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make()
            ->each(function ($item) use ($include) {
                $related = factory(Price::class)->make([
                    'PHOTO_ID' => $item->getKey(),
                ]);

                $item->setRelation($include, $related);
            });

        // Creates a request
        $request = $this->createRequest('photos', 'GET', ['include' => $include]);

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($include) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->loadRelationship($include, 'price');
            })
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObjectCollection($data, $strict);
        Assert::assertResourceCollectionEquals($expected, $data);
    }

    /**
     * @test
     */
    public function exportCollectionWithEmptyToManyRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        $include = 'comments';

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make()
            ->each(function ($item) use ($include) {
                $related = collect([]);
                $item->setRelation($include, $related);
            });

        // Creates a request
        $request = $this->createRequest('photos', 'GET', ['include' => $include]);

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($include) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->loadRelationship($include, 'comment');
            })
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObjectCollection($data, $strict);
        Assert::assertResourceCollectionEquals($expected, $data);
    }

    /**
     * @test
     */
    public function exportCollectionWithToManyRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        $include = 'comments';

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make()
            ->each(function ($item) use ($include) {
                $related = factory(
                    Comment::class,
                    rand(1, 3)
                )->make([
                    'PHOTO_ID' => $item->getKey(),
                ]);
                $item->setRelation($include, $related);
            });

        // Creates a request
        $request = $this->createRequest('photos', 'GET', ['include' => $include]);

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($include) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->loadRelationship($include, 'comment');
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
