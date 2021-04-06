<?php

namespace VGirol\JsonApi\Tests\Unit\Services\RelationshipService;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApi500Exception;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;
use VGirol\PhpunitException\SetExceptionsTrait;

class RelationshipServiceTest extends TestCase
{
    use UsesTools;
    use SetExceptionsTrait;

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
        $this->setUpToolsDB();
    }

    /**
     * @test
     */
    public function createToOneRelationshipWithCollection()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class, 2)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Set expected exception
        $this->setFailure(JsonApiException::class, RelationshipService::ERROR_BAD_TYPE);

        // Launch method to test
        $service->create($parent->price(), $related);
    }

    /**
     * @test
     */
    public function saveAll()
    {
        // Create objects with filled out fields
        $photo = factory(Photo::class)->create();
        $author = factory(Author::class)->create();
        $countTags = 5;
        $tags = factory(Tags::class, $countTags)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Create request data
        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => $photo->attributesToArray(),
            Members::RELATIONSHIPS => [
                'author' => [
                    Members::DATA => [
                        Members::TYPE => 'author',
                        Members::ID => $author->getKey()
                    ]
                ],
                'tags' => [
                    Members::DATA => $tags->map(function ($item) {
                        return [
                            Members::TYPE => 'tag',
                            Members::ID => $item->getKey()
                        ];
                    })->toArray()
                ]
            ]
        ];

        // Check initial state
        PHPUnit::assertCount(0, $photo->author()->get());
        PHPUnit::assertCount(0, $photo->tags()->get());

        // Launch method to test
        $service->saveAll($data, $photo);

        PHPUnit::assertCount(1, $photo->author()->get());
        PHPUnit::assertTrue($author->is($photo->author()->getResults()));
        PHPUnit::assertCount($countTags, $photo->tags()->get());
        foreach ($tags as $item) {
            PHPUnit::assertTrue($photo->tags()->get()->contains($item));
        }
    }

    /**
     * @test
     */
    public function saveAllButNoRelationships()
    {
        // Create objects with filled out fields
        $photo = factory(Photo::class)->make();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Create request data
        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => $photo->attributesToArray()
        ];

        // Check initial state
        $this->assertDatabaseMissing($photo->getTable(), $photo->attributesToArray());

        // Launch method to test
        $service->saveAll($data, $photo);

        $this->assertDatabaseMissing($photo->getTable(), $photo->attributesToArray());
    }

    /**
     * @test
     */
    public function updateAll()
    {
        // Create objects with filled out fields
        $photo = factory(Photo::class)->create();
        $author = factory(Author::class)->create();
        $newAuthor = factory(Author::class)->create();
        $countTags = 5;
        $tags = factory(Tags::class, $countTags)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach related to parent
        $service->create($photo->author(), $author);
        $service->create($photo->tags(), $tags);

        // Create request data
        $data = [
            Members::TYPE => 'photo',
            Members::ID => $photo->getKey(),
            Members::RELATIONSHIPS => [
                'author' => [
                    Members::DATA => [
                        Members::TYPE => 'author',
                        Members::ID => $newAuthor->getKey()
                    ]
                ]
            ]
        ];

        // Check initial state
        PHPUnit::assertCount(1, $photo->author()->get());
        PHPUnit::assertTrue($author->is($photo->author()->getResults()));
        PHPUnit::assertCount($countTags, $photo->tags()->get());
        foreach ($tags as $item) {
            PHPUnit::assertTrue($photo->tags()->get()->contains($item));
        }

        // Launch method to test
        $service->updateAll($data, $photo);

        PHPUnit::assertCount(1, $photo->author()->get());
        PHPUnit::assertTrue($newAuthor->is($photo->author()->getResults()));
        PHPUnit::assertCount($countTags, $photo->tags()->get());
        foreach ($tags as $item) {
            PHPUnit::assertTrue($photo->tags()->get()->contains($item));
        }
    }

    /**
     * @test
     */
    public function notRelationInstance()
    {
        // Create objects with filled out fields
        $relation = new class () {
            // nothing
        };
        $related = factory(Price::class)->make();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Set expected exception
        $this->setFailure(JsonApi500Exception::class, RelationshipService::ERROR_NOT_A_RELATION);

        // Launch method to test
        $service->create($relation, $related);
    }
}
