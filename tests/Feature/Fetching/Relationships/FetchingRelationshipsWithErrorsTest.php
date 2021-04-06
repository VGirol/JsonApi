<?php

namespace VGirol\JsonApi\Tests\Feature\Fetching\Relationships;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class FetchingRelationshipsWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Fetching relationships of model that does not exist
     * Should return 404
     *
     * @test
     */
    public function fetchingRelationshipsOfModelThatDoesNotExist()
    {
        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => 666, 'relationship' => 'relatedToOne']);
        $response = $this->jsonApi('GET', $url);

        // Check response status code
        $response->assertJsonApiResponse404([
            [
                Members::ERROR_STATUS => '404',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                Members::ERROR_DETAILS => sprintf(Messages::FETCHING_REQUEST_NOT_FOUND, 666)
            ]
        ]);
    }

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Fetching relationship that does not exist
     * Should return 404
     *
     * @test
     */
    public function fetchingRelationshipsThatDoesNotExist()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        $name = 'nonExistent';

        // Sends request and gets response
        $url = route('photos.relationship.index', ['parentId' => $model->getKey(), 'relationship' => $name]);
        $response = $this->jsonApi('GET', $url);

        // Check response status code
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => sprintf(Messages::NON_EXISTENT_RELATIONSHIP, $name)
            ]
        ]);
    }
}
