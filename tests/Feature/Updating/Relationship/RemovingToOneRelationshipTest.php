<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Relationship;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApiConstant\Members;

class RemovingToOneRelationshipTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship
     * Should return 200 with data array
     *
     * @test
     */
    public function removingToOneRelationshipBelongsTo()
    {
        // Creates an object with filled out fields
        $old = factory(Author::class)->create();
        $model = factory(Photo::class)->create([
            $old->getKeyName() => $old->getKey()
        ]);

        // Creates content of the request
        $content = [
            Members::DATA => null
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());

        // Sends request and gets response
        $url = route('photos.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'author']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Updates expected model
        $model->setAttribute('AUTHOR_ID', null);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated(null, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'author']
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
    public function removingToOneRelationshipHasOne()
    {
        // Creates an object with filled out fields
        $price = factory(Price::class)->create();
        $model = factory(Photo::class)->create();
        $model->price()->save($price);

        // Creates content of the request
        $content = [
            Members::DATA => null
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($price->getTable(), $price->attributesToArray());

        // Sends request and gets response
        $url = route('photos.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'price']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated(null, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseMissing($price->getTable(), $price->attributesToArray());

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'price']
            )
        ]);
    }
}
