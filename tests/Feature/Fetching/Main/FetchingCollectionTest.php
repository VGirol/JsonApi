<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Main;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class FetchingCollectionTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint
     * Should return 200 with empty data array
     *
     * @test
     */
    public function fetchingEmptyResourceCollection()
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Sends request and gets response
        $url = route('photos.index');
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $collection = collect([]);
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedResourceCollection($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }

    /**
     * GET /endpoint
     * Should return 200 with resource object list
     *
     * @test
     * @dataProvider fetchResourceCollectionProvider
     */
    public function fetchingResourceCollection($count)
    {
        // Set config
        config()->set('jsonapi.pagination.allowed', false);

        // Creates collection
        $collection = factory(Photo::class, $count)->create();
        $collection = $collection->sortBy('PHOTO_ID')->values();

        // Sends request and gets response
        $url = route('photos.index');
        $response = $this->jsonApi('GET', $url);

        // Creates the expected collection
        $expected = (new HelperFactory())->roCollection($collection, 'photo')
            ->each(function ($resource) {
                $resource->addLinks([
                    Members::LINK_SELF => route('photos.show', ['id' => $resource->getKey()])
                ]);
            })
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiFetchedResourceCollection($expected);

        // Checks that the response has no pagination (links and meta object)
        $response->assertJsonApiNoPagination();

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([Members::LINK_SELF => $url]);
    }

    public function fetchResourceCollectionProvider()
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
