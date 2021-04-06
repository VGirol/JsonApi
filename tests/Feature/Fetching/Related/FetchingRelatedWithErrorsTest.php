<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Related;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;

class FetchingRelatedWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{parentId}/{relationship}
     * Should return 404
     *
     * @test
     */
    public function fetchingRelatedCollectionOfModelThatDoesNotExist()
    {
        // Sends request and gets response
        $url = route(
            'photos.related.index',
            [
                'parentId' => 666,
                'relationship' => 'tags'
            ]
        );
        $response = $this->jsonApi('GET', $url);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse404(
            [
                [
                    Members::ERROR_STATUS => '404',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                    Members::ERROR_DETAILS => sprintf(Messages::FETCHING_REQUEST_NOT_FOUND, 666)
                ]
            ]
        );
    }
}
