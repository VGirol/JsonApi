<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Collection;

use VGirol\JsonApi\Resources\ResourceObjectCollection;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\CanTestIncludes;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceObjectCollectionToResponseWithIncludedTest extends TestCase
{
    use CanCreateRequest;
    use CanTestIncludes;
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
    public function exportResourceObjectCollection()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        config()->set('jsonapi.pagination.allowed', false);

        $relationships = [
            'tags',
            // 'comments.user'
        ];

        // Creates an object with filled out fields
        $count = 3;
        $tags = factory(Tags::class, 5)->make();
        $collection = factory(Photo::class, $count)->make()->each(function ($photo) use ($tags) {
            $authors = factory(Author::class, 2)->make();
            $comments = factory(Comment::class, 3)->make([
                'PHOTO_ID' => $photo->getKey()
            ])->each(function ($comment) use ($authors) {
                $author = $authors->random(1)->first();
                $comment->setAttribute('AUTHOR_ID', $author->AUTHOR_ID);
                $comment->setRelation('user', $author);
            });
            $photo->setRelation('comments', $comments);
            $photo->setRelation(
                'tags',
                $tags->random(rand(1, 3))
            );
        });

        // Creates a request
        $request = $this->createRequest(
            'photos',
            'GET',
            [
                'include' => implode(',', $relationships)
            ]
        );

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, $request->getIncludes());

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.index',
                [
                    'include' => implode(',', $relationships)
                ]
            )
        ];
        $expectedData = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($relationships) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->appendRelationships($relationships);
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

        // Iterates through required includes
        $this->checkAllIncludes($collection, $relationships, function ($expected) use ($json) {
            Assert::assertDocumentContainsInclude($expected, $json);
        });
    }

    /**
     * @test
     */
    public function exportResourceObjectCollectionWithEmptyRelationship()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        config()->set('jsonapi.pagination.allowed', false);

        $relationships = ['tags'];

        // Creates an object with filled out fields
        $count = 3;
        $collection = factory(Photo::class, $count)->make()->each(function ($item) {
            $item->setRelation(
                'tags',
                collect([])
            );
        });

        // Creates a request
        $request = $this->createRequest(
            'photos',
            'GET',
            [
                'include' => implode(',', $relationships)
            ]
        );

        // Creates a resource
        $resource = ResourceObjectCollection::make($collection, $request->getIncludes());

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.index',
                [
                    'include' => implode(',', $relationships)
                ]
            )
        ];
        $expectedData = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($item) use ($relationships) {
                $item->setRouteName('photos')
                    ->addLink(Members::LINK_SELF, route('photos.show', ['id' => $item->getKey()]))
                    ->appendRelationships($relationships);
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

        // Iterates through required includes
        Assert::assertNotHasMember(Members::INCLUDED, $json);
    }
}
