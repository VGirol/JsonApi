<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Relationship;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;

class UpdatingToOneRelationshipWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship whose parent does not exist
     * Should return 404 with errors
     *
     * @test
     */
    public function updatingRelationshipWhoseParentDoesNotExist()
    {
        // Creates an object with filled out fields
        $author = factory(Author::class)->create();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'author',
                Members::ID => $author->getKey()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'PATCH',
            route(
                'photos.relationship.update',
                [
                    'parentId' => 666,
                    'relationship' => 'author'
                ]
            ),
            $content
        );

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
     * GET /endpoint/{id}/relationships/{relationship}
     * Updating relationship that does not exist
     * Should return 404 with errors
     *
     * @test
     */
    public function updatingRelationshipThatDoesNotExist()
    {
        // Creates an object with filled out fields
        $parent = factory(Photo::class)->create();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'wrong',
                Members::ID => '666'
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'PATCH',
            route(
                'photos.relationship.update',
                [
                    'parentId' => $parent->getKey(),
                    'relationship' => 'wrong'
                ]
            ),
            $content
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse403(
            [
                [
                    Members::ERROR_STATUS => '403',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                    Members::ERROR_DETAILS => sprintf(Messages::NON_EXISTENT_RELATIONSHIP, 'wrong')
                ]
            ]
        );
    }
}
