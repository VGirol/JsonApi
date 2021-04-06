<?php

namespace VGirol\JsonApi\Tests\Feature\Deleting\Main;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;

class DeletingResourceWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * DELETE /endpoint/{id}
     * Deletes non existing resource
     * Should return 404
     *
     * @test
     */
    public function destroyNonExistingResource()
    {
        $key = 666;

        // Sends request and gets response
        $response = $this->jsonApi('DELETE', route('photos.destroy', ['id' => $key]));

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse404(
            [
                [
                    Members::ERROR_STATUS => '404',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                    Members::ERROR_DETAILS => sprintf(Messages::FETCHING_REQUEST_NOT_FOUND, $key)
                ]
            ]
        );
    }
}
