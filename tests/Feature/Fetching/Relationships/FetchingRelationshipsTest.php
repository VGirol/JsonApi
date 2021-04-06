<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Relationships;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class FetchingRelationshipsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Fetching empty to-one relationship
     * Should return 200 with data = null
     *
     * @test
     */
    public function fetchingEmptyToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => $model->getKey(), 'relationship' => 'price']);
        $response = $this->jsonApi('GET', $url);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedRelationships(null);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'price']
            )
        ]);
    }

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Fetching to-one relationship
     * Should return 200 with single resource linkage object
     *
     * @test
     */
    public function fetchingToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates collection
        $related = factory(Price::class)->create(['PHOTO_ID' => $model->getKey()]);

        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => $model->getKey(), 'relationship' => 'price']);
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $resourceFactory = (new HelperFactory())->resourceIdentifier($related, 'price');

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedRelationships($resourceFactory->toArray());

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'price']
            )
        ]);
    }

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Fetching to-many relationship
     * Should return 200 with array of resource linkage object
     *
     * @test
     */
    public function fetchingToManyRelationships()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates a collection of related objects
        $count = 5;
        factory(Tags::class, $count)->create();

        // Get all the tags
        $tags = Tags::all();

        // Attach related objects to main object : Populate the pivot table
        $count = 3;
        Photo::all()->each(function ($photo) use ($tags) {
            $photo->tags()->attach(
                $tags->random(rand(1, 3))->pluck('TAGS_ID')->toArray()
            );
        });
        $collection = Photo::find($model->getKey())->tags();
        $collection = $collection->orderBy('TAGS_ID')->get();

        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => $model->getKey(), 'relationship' => 'tags']);
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $expected = (new HelperFactory())->riCollection($collection, 'tag')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedRelationships($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

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
     * Fetching empty to-many relationship
     * Should return 200 with empty array
     *
     * @test
     */
    public function fetchingEmptyToManyRelationships()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => $model->getKey(), 'relationship' => 'tags']);
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $expected = (new HelperFactory())->riCollection(collect([]), 'tags')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedRelationships($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url,
            Members::LINK_RELATED => route(
                'photos.related.index',
                ['parentId' => $model->getKey(), 'relationship' => 'tags']
            )
        ]);
    }
}
