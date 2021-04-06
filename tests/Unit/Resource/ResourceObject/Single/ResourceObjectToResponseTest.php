<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Single;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceObjectToResponseTest extends TestCase
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

        $relationshipName = 'price';

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = null;
        $model->setRelation($relationshipName, $related);

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}/{$relationshipName}",
            'GET'
        );

        // Creates a resource
        $resource = ResourceObject::make($related);

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
        PHPUnit::assertNull($json[Members::DATA]);

        // Checks the top-level links object
        Assert::assertHasLinks($json);
        $links = $json[Members::LINKS];
        Assert::assertLinksObjectEquals($expectedLinks, $links);
    }

    /**
     * @test
     */
    public function exportResourceObject()
    {
        // Set config
        config()->set('jsonapi.include.allowed', false);

        $relationshipName = 'price';

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(Price::class)->make([
            'PHOTO_ID' => $model->getKey(),
        ]);
        $model->setRelation($relationshipName, $related);

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}/{$relationshipName}",
            'GET'
        );

        // Creates a resource
        $resource = ResourceObject::make($related);

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            )
        ];
        $expectedData = (new HelperFactory())->resourceObject($related, 'price', 'prices')
            ->addSelfLink()
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
