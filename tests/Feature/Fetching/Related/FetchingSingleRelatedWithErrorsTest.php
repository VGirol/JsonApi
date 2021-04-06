<?php

namespace VGirol\JsonApi\Tests\Feature\Related\Show;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class FetchingSingleRelatedWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{parentId}/{relationship}/{id}
     * Should return 404 with errors
     *
     * @test
     */
    public function fetchingSingleRelatedOfModelThatDoesNotExist()
    {
        // Sends request and gets response
        $url = route(
            'photos.related.show',
            [
                'parentId' => 666,
                'relationship' => 'author',
                'id' => 1
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

    /**
     * GET /endpoint/{parentId}/{relationship}/{id}
     * Should return 404 with errors
     *
     * @test
     */
    public function fetchingSingleRelatedThatDoesNotExist()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $url = route(
            'photos.related.show',
            [
                'parentId' => $model->getKey(),
                'relationship' => 'tags',
                'id' => 666
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
