<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Related;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class FetchingRelatedAsCollectionTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{parentId}/{relationship}
     * Should return 200 with empty data array
     *
     * @test
     */
    public function fetchingEmptyRelatedCollection()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates model
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $url = route('photos.related.index', ['parentId' => $model->getKey(), 'relationship' => 'tags']);
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $collection = collect([]);
        $expected = (new HelperFactory())->roCollection($collection, 'tags')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedResourceCollection($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }

    /**
     * GET /endpoint/{parentId}/{relationship}
     * Should return 200 with resource object list
     *
     * @test
     * @dataProvider fetchingRelatedCollectionProvider
     */
    public function fetchingRelatedCollection($count)
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates a collection of related objects
        $related = factory(Tags::class, $count)->create();

        // Attach related objects to main object : Populate the pivot table
        $model->tags()->attach(
            $related->pluck('TAGS_ID')->toArray()
        );
        $collection = $model->tags()->get();

        // Sends request and gets response
        $url = route('photos.related.index', ['parentId' => $model->getKey(), 'relationship' => 'tags']);
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $expected = (new HelperFactory())->roCollection($collection, 'tag')
            ->each(
                /**
                 * @param \VGirol\JsonApi\Tests\Tools\Factory\ResourceObjectFactory $resource
                 */
                function ($resource) {
                    $resource->setRouteName('tags')
                    ->addSelfLink()
                    ->addAttribute('PIVOT_COMMENT', $resource->getModel()->pivot->PIVOT_COMMENT);
                }
            )
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedResourceCollection($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }

    public function fetchingRelatedCollectionProvider()
    {
        return [
            'single item' => [
                1
            ],
            'multi items' => [
                5
            ]
        ];
    }
}
