<?php

namespace VGirol\JsonApi\Tests\Feature\Main\Show;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;

class FetchingResourceWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}
     * Should return 404 with errors
     *
     * @test
     */
    public function fetchingSingleResourceThatDoesNotExist()
    {
        // Sends request and gets response
        $response = $this->jsonApi('GET', route('photos.show', ['id' => 666]));

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
