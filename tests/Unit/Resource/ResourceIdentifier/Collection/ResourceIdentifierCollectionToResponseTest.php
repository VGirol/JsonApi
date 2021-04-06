<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceIdentifier\Single;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceIdentifierCollectionToResponseTest extends TestCase
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
    public function exportEmptyCollectionAsPrimaryData()
    {
        $relationshipName = 'comments';

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = collect([]);
        $model->setRelation($relationshipName, $related);

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}/relationships/{$relationshipName}",
            'GET'
        );

        // Creates a resource
        $resource = ResourceIdentifierCollection::make($related);

        // Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.relationship.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            ),
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            )
        ];

        // Exports model
        $json = $resource->response($request)->getData(true);

        // Checks the top-level data object
        Assert::assertHasData($json);
        PHPUnit::assertEquals([], $json[Members::DATA]);

        // Checks the top-level links object
        Assert::assertHasLinks($json);
        $links = $json[Members::LINKS];
        Assert::assertLinksObjectEquals($expectedLinks, $links);
    }

    /**
     * @test
     */
    public function exportCollectionAsPrimaryData()
    {
        $relationshipName = 'comments';

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $count = 3;
        $related = factory(Comment::class, $count)->make([
            'PHOTO_ID' => $model->getKey(),
        ]);
        $model->setRelation($relationshipName, $related);

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}/relationships/{$relationshipName}",
            'GET'
        );

        // Creates a resource
        $resource = ResourceIdentifierCollection::make($related);

        // Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.relationship.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            ),
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => $relationshipName]
            )
        ];
        $expectedData = (new HelperFactory())->riCollection($related, 'comment')
            ->toArray();

        // Exports model
        $json = $resource->response($request)->getData(true);

        // Checks the top-level data object
        Assert::assertHasData($json);
        $data = $json[Members::DATA];
        Assert::assertResourceIdentifierCollectionEquals($expectedData, $data);

        // Checks the top-level links object
        Assert::assertHasLinks($json);
        $links = $json[Members::LINKS];
        Assert::assertLinksObjectEquals($expectedLinks, $links);
    }
}
