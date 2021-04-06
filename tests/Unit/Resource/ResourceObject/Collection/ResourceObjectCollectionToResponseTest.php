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

class ResourceObjectCollectionToResponseTest extends TestCase
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
        config()->set('jsonapi.pagination.allowed', false);

        $relationshipName = 'comments';

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = collect([]);
        $model->setRelation($relationshipName, $related);

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}/{$relationshipName}",
            'GET'
        );

        // Creates a resource
        $resource = ResourceObjectCollection::make($related);

        // Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            )
        ];

        // Exports model
        $json = $resource->response($request)->getData(true);

        // Checks the top-level data object
        Assert::assertHasData($json);
        PHPUnit::assertIsArray($json[Members::DATA]);
        PHPUnit::assertEmpty($json[Members::DATA]);

        // Checks the top-level links object
        Assert::assertHasLinks($json);
        $links = $json[Members::LINKS];
        Assert::assertLinksObjectEquals($expectedLinks, $links);
    }

    /**
     * @test
     */
    public function exportResourceObjectCollection()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make();

        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection);

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route('photos.index')
        ];
        $expectedData = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) {
                $item->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]));
            })
            ->toArray();

        // Exports model
        $json = $resource->response($request)->getData(true);

        // Checks the top-level data object
        Assert::assertHasData($json);
        $data = $json[Members::DATA];
        Assert::assertResourceObjectEquals($expectedData, $data);

        // Checks the top-level links object
        Assert::assertHasLinks($json);
        $links = $json[Members::LINKS];
        Assert::assertLinksObjectEquals($expectedLinks, $links);
    }
}
