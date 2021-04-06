<?php

namespace VGirol\JsonApi\Tests\Feature\Deleting\Related;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class DeletingRelatedWithErrorsTest extends TestCase
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
        // Creates an object with filled out fields
        $model = factory(Author::class)->create();

        $key = 666;

        // Sends request and gets response
        $response = $this->jsonApi(
            'DELETE',
            route(
                'authors.related.destroy',
                [
                    'parentId' => $model->getKey(),
                    'relationship' => 'photos',
                    'id' => $key
                ]
            )
        );

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

    /**
     * DELETE /endpoint/{id}
     * Deletes a related resource whose parent does not exist
     * Should return 404
     *
     * @test
     */
    public function destroyRelatedWhoseParentDoesNotExist()
    {
        // Creates an object with filled out fields
        $related = factory(Photo::class)->create();

        $key = 666;

        // Sends request and gets response
        $response = $this->jsonApi(
            'DELETE',
            route(
                'authors.related.destroy',
                [
                    'parentId' => $key,
                    'relationship' => 'photos',
                    'id' => $related->getKey()
                ]
            )
        );

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
