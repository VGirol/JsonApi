<?php

namespace VGirol\JsonApi\Tests\Feature\BadRequest;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;

class BadEndpointTest extends TestCase
{
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpToolsRoutes();
    }

    /**
     * GET /endpoint
     * Should return 404
     *
     * @test
     */
    public function requestingEndpointThatDoesNotExist()
    {
        // Sends request and gets response
        $response = $this->jsonApi('GET', 'badEndPoint');

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse404(
            [
                [
                    Members::ERROR_STATUS => '404',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                    Members::ERROR_DETAILS => Messages::BAD_ENDPOINT
                ]
            ]
        );
    }
}
