<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Main;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class CreatingResourceAndRelationshipTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint
     * Creating resource with a single to-one relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingOneToOneRelationship()
    {
        // Sets config
        config(['jsonapi.creationAddLocationHeader' => true]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->makeVisible('AUTHOR_ID');
        $related = factory(Price::class)->create();

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'price' => [
                        Members::DATA => [
                            Members::TYPE => 'price',
                            Members::ID => strval($related->getKey())
                        ]
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('photos.show', ['id' => $model->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $related->setAttribute($model->getKeyName(), $model->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource with a single to-one relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingOneToOneInverseRelationship()
    {
        // Sets config
        config(['jsonapi.creationAddLocationHeader' => true]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $model->makeVisible('AUTHOR_ID');
        $related = factory(Price::class)->make();

        $resourceType = 'price';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $related->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'photo' => [
                        Members::DATA => [
                            Members::TYPE => 'photo',
                            Members::ID => strval($model->getKey())
                        ]
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('prices.store'), $content);

        // Creates the expected resource
        $related->setAttribute($model->getKeyName(), $model->getKey());
        $expected = (new HelperFactory())->resourceObject($related, $resourceType, 'prices')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('prices.show', ['id' => $related->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource with a single to-one relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingOneToManyRelationship()
    {
        // Sets config
        config(['jsonapi.creationAddLocationHeader' => true]);

        // Creates an object with filled out fields
        $model = factory(Author::class)->make();
        $related = factory(Photo::class, 3)->create([
            $model->getKeyName() => null
        ]);
        $related->each(
            function ($item) use ($model) {
                $item->makeVisible($model->getKeyName());
            }
        );

        $resourceType = 'author';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'photos' => [
                        Members::DATA => $related->map(
                            function ($item) {
                                return [
                                    Members::TYPE => 'photo',
                                    Members::ID => strval($item->getKey())
                                ];
                            }
                        )->toArray()
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
        $related->each(
            function ($item) {
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
            }
        );

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('authors.store'), $content);

        // Creates the expected resource
        $model = $model->refresh();
        $related->each(
            function ($item) use ($model) {
                $item->setAttribute($model->getKeyName(), $model->getKey());
            }
        );

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'authors')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('authors.show', ['id' => $model->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $related->each(
            function ($item) {
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
            }
        );

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource with many to-one relationships
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingMultiOneToManyInverseRelationship()
    {
        // Sets config
        config(['jsonapi.creationAddLocationHeader' => true]);

        // Creates an object with filled out fields
        $author = factory(Author::class)->create();
        $photo = factory(Photo::class)->create();

        // These foreign keys can not be null, so we set them to 0.
        // In real life, this case will never exist.
        $model = factory(Comment::class)->make([
            'PHOTO_ID' => 0,
            'AUTHOR_ID' => 0
        ]);

        $resourceType = 'comment';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'photo' => [
                        Members::DATA => [
                            Members::TYPE => 'photo',
                            Members::ID => strval($photo->getKey())
                        ]
                    ],
                    'user' => [
                        Members::DATA => [
                            Members::TYPE => 'author',
                            Members::ID => strval($author->getKey())
                        ]
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Creates the expected resource
        $model->setAttribute('COMMENT_ID', 1);
        $model->setAttribute('PHOTO_ID', $photo->getKey());
        $model->setAttribute('AUTHOR_ID', $author->getKey());
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'comments')
            ->addSelfLink()
            ->toArray();

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('comments.store'), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('comments.show', ['id' => $model->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource with a single to-one relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingManyToManyRelationship()
    {
        // Sets config
        config(['jsonapi.creationAddLocationHeader' => true]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(Tags::class, 5)->create();

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'tags' => [
                        Members::DATA => $related->map(
                            function ($item) {
                                return [
                                    Members::TYPE => 'tags',
                                    Members::ID => strval($item->getKey())
                                ];
                            }
                        )->toArray()
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
        $related->each(
            function ($item) use ($model) {
                $this->assertDatabaseMissing(
                    'pivot_phototags',
                    [
                        'PHOTO_ID' => $model->getKey(),
                        'TAGS_ID' => $item->getKey()
                    ]
                );
            }
        );

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('photos.show', ['id' => $model->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->toArray());
        $related->each(
            function ($item, $index) use ($model) {
                $this->assertDatabaseHas(
                    'pivot_phototags',
                    [
                        'PIVOT_ID' => $index + 1,
                        'PHOTO_ID' => $model->getKey(),
                        'TAGS_ID' => $item->getKey()
                    ]
                );
            }
        );

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }
}
