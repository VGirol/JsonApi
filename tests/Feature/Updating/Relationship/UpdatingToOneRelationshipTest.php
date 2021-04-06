<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Relationship;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApiConstant\Members;

class UpdatingToOneRelationshipTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship
     * Should return 200 with data array
     *
     * @test
     */
    public function updatingBelongsToRelationship()
    {
        // Creates an object with filled out fields
        $old = factory(Author::class)->create();
        $model = factory(Photo::class)->create([
            $old->getKeyName() => $old->getKey()
        ]);
        $new = factory(Author::class)->create();

        $relatedResourceType = 'author';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ID => $new->getKey()
            ]
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($new->getTable(), $new->attributesToArray());

        // Sends request and gets response
        $url = route('photos.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'author']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Updates expected model
        $model->setAttribute('AUTHOR_ID', $new->getKey());

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceIdentifier($new, $relatedResourceType, 'authors')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($new->getTable(), $new->attributesToArray());

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
    public function updatingHasOneRelationship()
    {
        // Creates an object with filled out fields
        $old = factory(Price::class)->create();
        $model = factory(Photo::class)->create();
        $model->price()->save($old);
        $new = factory(Price::class)->create();

        $relatedResourceType = 'price';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ID => $new->getKey()
            ]
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($new->getTable(), $new->attributesToArray());

        // Sends request and gets response
        $url = route('photos.relationship.update', ['parentId' => $model->getKey(), 'relationship' => 'price']);
        $response = $this->jsonApi('PATCH', $url, $content);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceIdentifier($new, $relatedResourceType, 'prices')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected, true);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $old->setAttribute($model->getKeyName(), null);
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($new->getTable(), $new->attributesToArray());

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
