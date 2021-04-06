<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Main;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;

class FetchingResourceTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}
     * Should return 200 with data
     *
     * @test
     */
    public function fetchingSingleResource()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Defines variables
        $url = route('photos.show', ['id' => $model->getKey()]);

        // Sends request and gets response
        $response = $this->jsonApi('GET', $url);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink();

        // Checks the response (status code, headers) and the fetched resource
        $response->assertJsonApiFetchedSingleResource($expected->toArray());

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals($expected->getLinks());
    }
}
