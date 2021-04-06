<?php

namespace VGirol\JsonApi\Tests\Feature\Main\Update;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\CanTestIncludes;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;

class UpdatingResourceAndRelationshipsWithErrorsTest extends TestCase
{
    use CompleteSetUp;
    use CanTestIncludes;

    /**
     * PATCH /endpoint/{id}
     * Updating resource with invalid payloads
     * Should return 403 with errors
     *
     * @test
     */
    public function updatingResourceInexistantRelationship()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $model->tags()->attach(
            factory(Tags::class, 3)->create()
        );
        $tag = factory(Tags::class)->create();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Creates content of the request
        $name = 'inexistant';
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    $name => [
                        Members::DATA => [
                            [
                                Members::TYPE => 'tag',
                                Members::ID => strval($tag->getKey())
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse403(
            [
                [
                    Members::ERROR_STATUS => '403',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                    Members::ERROR_DETAILS => sprintf(Messages::NON_EXISTENT_RELATIONSHIP, $name)
                ]
            ]
        );
    }

    /**
     * PATCH /endpoint/{id}
     * Updating relationship while full replacement is not allowed
     * Should return 403 with errors
     *
     * @test
     */
    public function updatingRelationshipWhileFullReplacementIsNotAllowed()
    {
        // Sets config
        config([
            'jsonapi.relationshipFullReplacementIsAllowed' => false
        ]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $model->tags()->attach(
            factory(Tags::class, 3)->create()
        );
        $tag = factory(Tags::class)->create();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::RELATIONSHIPS => [
                    'tags' => [
                        Members::DATA => [
                            [
                                Members::TYPE => 'tag',
                                Members::ID => strval($tag->getKey())
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse403(
            [
                [
                    Members::ERROR_STATUS => '403',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                    Members::ERROR_DETAILS => Messages::RELATIONSHIP_FULL_REPLACEMENT
                ]
            ]
        );
    }

    /**
     * PATCH /endpoint/{id}
     * Updating resource with related that does not exists
     * Should return 404 with errors
     *
     * @test
     */
    public function updatingResourceWithRelatedNotFound()
    {
        // Sets config
        config([
            'jsonapi.relationshipFullReplacementIsAllowed' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'tags' => [
                        Members::DATA => [
                            [
                                Members::TYPE => 'tag',
                                Members::ID => '666'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse404(
            [
                [
                    Members::ERROR_STATUS => '404',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                    Members::ERROR_DETAILS => sprintf(Messages::UPDATING_REQUEST_RELATED_NOT_FOUND, 'tag')
                ]
            ]
        );
    }
}
