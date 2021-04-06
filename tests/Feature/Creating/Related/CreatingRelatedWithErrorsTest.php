<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Related;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Exceptions\JsonApiDuplicateEntryException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class CreatingRelatedWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Creating single related resource with invalid payload
     * Should return 403 with errors
     *
     * @test
     * @dataProvider creatingRelatedWithInvalidPayloadProvider
     */
    public function creatingRelatedWithInvalidPayload($content, $failureMsg)
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('photos.related.store', ['parentId' => $model->getKey(), 'relationship' => 'price']),
            $content
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse403(
            [
                [
                    Members::ERROR_STATUS => '403',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                    Members::ERROR_DETAILS => $failureMsg
                ]
            ]
        );
    }

    public function creatingRelatedWithInvalidPayloadProvider()
    {
        return [
            'no data' => [
                [
                    Members::META => ['key' => 'value']
                ],
                Messages::REQUEST_ERROR_NO_DATA_MEMBER
            ],
            'data is null' => [
                [
                    Members::DATA => null
                ],
                Messages::REQUEST_ERROR_DATA_MEMBER_NULL
            ],
            'not single resource' => [
                [
                    Members::DATA => [
                        [
                            Members::TYPE => 'test'
                        ],
                        [
                            Members::TYPE => 'test'
                        ]
                    ]
                ],
                Messages::REQUEST_ERROR_DATA_MEMBER_NOT_SINGLE
            ],
            'no type member' => [
                [
                    Members::DATA => [
                        Members::META => ['key' => 'value']
                    ]
                ],
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT
            ]
        ];
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Creating related resource with invalid payload (invalid resource type)
     * Should return 409
     *
     * @test
     */
    public function creatingRelatedWithInvalidTypeOfResourceObject()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $related = factory(Comment::class)->make();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'badType',
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('authors.related.store', ['parentId' => $model->getKey(), 'relationship' => 'comments']),
            $content
        );

        // Check response
        $response->assertJsonApiResponse409([
            [
                Members::ERROR_STATUS => '409',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                Members::ERROR_DETAILS => "The given data was invalid.\nThe selected data.type is invalid."
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Create single related resource with parent that does not exist
     * Should return 404 with error
     *
     * @test
     */
    public function creatingRelatedWithParentThatDoesNotExist()
    {
        // Creates an object with filled out fields
        $related = factory(Photo::class)->make();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('photos.related.store', ['parentId' => 666, 'relationship' => 'photos']),
            $content
        );

        // Check response
        $response->assertJsonApiResponse404([
            [
                Members::ERROR_STATUS => '404',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                Members::ERROR_DETAILS => sprintf(Messages::FETCHING_REQUEST_NOT_FOUND, 666)
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Creating single related resource with client-generated ID although not allowed
     * Should return 403
     *
     * @test
     */
    public function creatingRelatedWithClientGeneratedIdNotAllowed()
    {
        // Sets config
        config([
            'jsonapi.clientGeneratedIdIsAllowed' => false
        ]);

        // Creates an object with filled out fields
        $model = factory(Author::class)->create();
        $related = factory(Photo::class)->make([$model->getKeyName() => null]);
        $related->makeVisible('AUTHOR_ID');
        $related->setAttribute('PHOTO_ID', 456);

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($related->getKey()),
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('authors.related.store', ['parentId' => $model->getKey(), 'relationship' => 'photos']),
            $content
        );

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => Messages::CLIENT_GENERATED_ID_NOT_ALLOWED
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Creating related resource with client-generated ID and invalid payload (conflicting ID)
     * Should return 409
     *
     * @test
     */
    public function creatingRelatedWithConflictingClientGeneratedId()
    {
        // Sets config
        config([
            'jsonapi.clientGeneratedIdIsAllowed' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Author::class)->create();
        $oldRelated = factory(Photo::class)->create();
        $related = factory(Photo::class)->make([$model->getKeyName() => null]);
        $related->makeVisible('AUTHOR_ID');
        $related->setAttribute('PHOTO_ID', $oldRelated->getKey());

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($related->getKey()),
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('authors.related.store', ['parentId' => $model->getKey(), 'relationship' => 'photos']),
            $content
        );

        // Check response
        $response->assertJsonApiResponse409([
            [
                Members::ERROR_STATUS => '409',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                Members::ERROR_DETAILS => JsonApiDuplicateEntryException::MESSAGE
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }
}
