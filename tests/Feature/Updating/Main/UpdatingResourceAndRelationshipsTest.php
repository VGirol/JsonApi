<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Main;

use VGirol\JsonApi\Tests\CanTestIncludes;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class UpdatingResourceAndRelationshipsTest extends TestCase
{
    use CompleteSetUp;
    use CanTestIncludes;

    /**
     * PATCH /endpoint/{id}
     * Updating resource and one of its relationships
     * Should return 204 with no content
     *
     * @test
     */
    public function updatingResourceAndRelationships()
    {
        // Creates an object with filled out fields
        $old = factory(Author::class)->create();
        $author = factory(Author::class)->create();
        $tags = factory(Tags::class, 3)->create();
        $model = factory(Photo::class)->create();
        $model->tags()->attach($tags);
        $model->author()->associate($old);
        $model->save();

        // Checks the database
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($author->getTable(), $author->attributesToArray());
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $tags->each(
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

        // Update model
        $model->setAttribute('PHOTO_TITLE', 'new value');

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => [
                    'PHOTO_TITLE' => $model->PHOTO_TITLE
                ],
                Members::RELATIONSHIPS => [
                    'author' => [
                        Members::DATA => [
                            Members::TYPE => 'author',
                            Members::ID => strval($author->getKey())
                        ]
                    ]
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Creates the expected resource
        $model->setAttribute('AUTHOR_ID', $author->getKey());
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected->toArray());

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $tags->each(
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
        $response->assertJsonApiDocumentLinksObjectEquals($expected->getLinks());
    }
}
