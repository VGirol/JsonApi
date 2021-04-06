<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Related;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class FetchingRelatedAsSingleTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{parentId}/{relationship}
     * Should return 200 with data
     *
     * @test
     */
    public function fetchingSingleRelated()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates a collection of related objects
        $related = factory(Author::class)->create();

        // Attach related objects to main object : Populate the pivot table
        $model->author()->associate($related);
        $model->save();
        $related = $model->author;

        // Sends request and gets response
        $url = route(
            'photos.related.index',
            [
                'parentId' => $model->getKey(),
                'relationship' => 'author'
            ]
        );
        $response = $this->jsonApi('GET', $url);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($related, 'author', 'authors')
            ->addSelfLink();

        // Checks the response (status code, headers) and the fetched resource
        $response->assertJsonApiFetchedSingleResource($expected->toArray());

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }

    /**
     * GET /endpoint/{parentId}/{relationship}
     * Should return 200 with data
     *
     * @test
     */
    public function fetchingSingleRelatedAsNull()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $url = route(
            'photos.related.index',
            [
                'parentId' => $model->getKey(),
                'relationship' => 'author'
            ]
        );
        $response = $this->jsonApi('GET', $url);

        // Checks the response (status code, headers) and the fetched resource
        $response->assertJsonApiFetchedSingleResource(null);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }
}
