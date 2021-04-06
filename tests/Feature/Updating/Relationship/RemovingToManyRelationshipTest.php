<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Relationship;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class RemovingToManyRelationshipTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship
     * Should return 200 with data array
     *
     * @test
     */
    public function updatingBelongsToManyRelationship()
    {
        // Sets config
        config(['jsonapi.relationshipFullReplacementIsAllowed' => true]);

        // Creates an object with filled out fields
        $old = factory(Tags::class, 3)->create();
        $model = factory(Photo::class)->create();
        $model->tags()->sync($old);

        // Creates content of the request
        $content = [
            Members::DATA => []
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $old->each(
            function ($item) use ($model) {
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
                $this->assertDatabaseHas(
                    'pivot_phototags',
                    [
                        $model->getKeyName() => $model->getKey(),
                        $item->getKeyName() => $item->getKey()
                    ]
                );
            }
        );

        // Sends request and gets response
        $url = route('photos.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'tags']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Creates the expected resource
        $expected = (new HelperFactory())->riCollection([], 'tag')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $old->each(
            function ($item) use ($model) {
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
                $this->assertDatabaseMissing(
                    'pivot_phototags',
                    [
                        $model->getKeyName() => $model->getKey(),
                        $item->getKeyName() => $item->getKey()
                    ]
                );
            }
        );

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'tags']
            )
        ]);
    }

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship
     * Should return 200 with data array
     *
     * @test
     */
    public function updatingHasManyRelationship()
    {
        // Sets config
        config(['jsonapi.relationshipFullReplacementIsAllowed' => true]);

        // Creates an object with filled out fields
        $old = factory(Photo::class, 3)->create();
        $model = factory(Author::class)->create();
        $model->photos()->saveMany($old);

        // Creates content of the request
        $content = [
            Members::DATA => []
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $old->each(
            function ($item) use ($model) {
                $item->setAttribute($model->getKeyName(), $model->getKey());
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
            }
        );

        // Sends request and gets response
        $url = route('authors.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'photos']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Creates the expected resource
        $old->each(
            function ($item) use ($model) {
                $item->setAttribute($model->getKeyName(), null);
            }
        );
        $expected = (new HelperFactory())->riCollection([], 'photo')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $old->each(
            function ($item) use ($model) {
                $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
            }
        );

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'authors.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'photos']
            )
        ]);
    }
}
