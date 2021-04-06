<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Single;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceObject;
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

class ResourceObjectToResponseWithIncludedTest extends TestCase
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
    public function exportResourceObject()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Add "tags" relationship
        $tags = factory(Tags::class, 5)->make();
        $model->setRelation(
            'tags',
            // $tags->random(rand(1, 3))->pluck('TAGS_ID')->toArray()
            $tags->random(rand(1, 3))
        );

        // // Add "comments" relationship
        // $authors = factory(Author::class, 2)->make();
        // $comments = factory(Comment::class, 3)->make([
        //     'PHOTO_ID' => $model->getKey()
        // ])->each(function ($item) use ($authors) {
        //     $author = $authors->random(1)->first();
        //     $item->setAttribute('AUTHOR_ID', $author->AUTHOR_ID);
        //     $item->setRelation('user', $author);
        // });
        // $model->setRelation('comments', $comments);

        // // Add "comments.user" relationship
        // $model->setRelation('comments.user', $model->comments()->user());

        // Assert relationships are loaded
        PHPUnit::assertTrue($model->relationLoaded('tags'));
        // PHPUnit::assertTrue($model->relationLoaded('comments'));
        // $model->comments->each(
        //     function($item, $key) {
        //         PHPUnit::assertTrue($item->relationLoaded('user'));
        //     }
        // );
        // PHPUnit::assertTrue($model->relationLoaded('comments.user'));

        // Define relationships to include
        $relationships = [
            'tags',
            // 'comments.user'
        ];

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}",
            'GET',
            [
                'include' => implode(',', $relationships)
            ]
        );

        // Creates a resource
        $resource = ResourceObject::make($model, $request->getIncludes());

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.show',
                [
                    Members::ID => $model->getKey(),
                    'include' => implode(',', $relationships)
                ]
            )
        ];
        $expectedData = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->appendRelationships($relationships)
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
        $this->checkAllIncludes($model, $relationships, function ($expected) use ($json) {
            Assert::assertDocumentContainsInclude($expected, $json);
        });
    }

    /**
     * @test
     */
    public function exportResourceObjectWithEmptyRelationship()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);

        $relationships = ['tags'];

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setRelation(
            'tags',
            collect([])
        );

        // Creates a request
        $request = $this->createRequest(
            "photos/{$model->getKey()}",
            'GET',
            [
                'include' => implode(',', $relationships)
            ]
        );

        // Creates a resource
        $resource = ResourceObject::make($model, $request->getIncludes());

        //Creates the expected objects
        $expectedLinks = [
            Members::LINK_SELF => route(
                'photos.show',
                [
                    Members::ID => $model->getKey(),
                    'include' => implode(',', $relationships)
                ]
            )
        ];
        $expectedData = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->appendRelationships($relationships)
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
