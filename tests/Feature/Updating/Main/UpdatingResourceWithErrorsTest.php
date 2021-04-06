<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Main;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages as VGirolMessages;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class UpdatingResourceWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * PATCH /endpoint/{id}
     * Updating resource with invalid payloads
     * Should return 403 with errors
     *
     * @test
     * @dataProvider updatingResourceInvalidPayloadProvider
     */
    public function updatingResourceInvalidPayload($content, $failureMsg)
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create(['PHOTO_ID' => 1]);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

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

    public function updatingResourceInvalidPayloadProvider()
    {
        return [
            'no data member' => [
                [
                    Members::META => ['key' => 'value']
                ],
                Messages::REQUEST_ERROR_NO_DATA_MEMBER
            ],
            'data member is null' => [
                [
                    Members::DATA => null
                ],
                Messages::REQUEST_ERROR_DATA_MEMBER_NULL
            ],
            'not single resource' => [
                [
                    Members::DATA => [
                        [
                            Members::ID => 1,
                            Members::TYPE => 'photo'
                        ],
                        [
                            Members::ID => 2,
                            Members::TYPE => 'photo'
                        ]
                    ]
                ],
                Messages::REQUEST_ERROR_DATA_MEMBER_NOT_SINGLE
            ],
            'no type member' => [
                [
                    Members::DATA => [
                        Members::ID => '1'
                    ]
                ],
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT
            ],
            'no id member' => [
                [
                    Members::DATA => [
                        Members::TYPE => 'photo'
                    ]
                ],
                JsonApiStructureMessages::RESOURCE_ID_MEMBER_IS_ABSENT
            ]
        ];
    }

    /**
     * PATCH /endpoint/{id}
     * Updating resource with invalid payload (invalid resource type)
     * Should return 409 with errors
     *
     * @test
     */
    public function updatingResourceWithInvalidTypeOfResourceObject()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        $content = [
            Members::DATA => [
                Members::TYPE => 'wrong',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse409(
            [
                [
                    Members::ERROR_STATUS => '409',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                    Members::ERROR_DETAILS => "The given data was invalid.\nThe selected data.type is invalid."
                ]
            ]
        );
    }

    /**
     * PATCH /endpoint/{id}
     * Updating resource with invalid payload (attributes not valid)
     * Should return 403 with errors
     *
     * @test
     */
    public function updatingResourceWithInvalidPayload()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => [
                    'PHOTO_TITLE' => null
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse403(
            [
                [
                    Members::ERROR_STATUS => '403',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                    Members::ERROR_DETAILS => "The given data was invalid.\n" .
                    'The ' . $this->formatAttribute('data.attributes.PHOTO_TITLE') . " must be a string.\n" .
                    'The ' . $this->formatAttribute('data.attributes.PHOTO_TITLE') . ' must not be null.'
                ]
            ]
        );
    }

    /**
     * PATCH /endpoint/{id}
     * Updating resource that does not exist
     * Should return 404 with errors
     *
     * @test
     */
    public function updatingResourceNotFound()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
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
                    Members::ERROR_DETAILS => sprintf(VGirolMessages::FETCHING_REQUEST_NOT_FOUND, $model->getKey())
                ]
            ]
        );

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * PATCH /endpoint/{id}
     * Updating resource with conflicting attributes
     * Should return 409 with errors
     *
     * @test
     */
    public function updatingResourceWithConflict()
    {
        // Creates an object with filled out fields
        $conflictingModel = factory(Photo::class)->create();
        $model = factory(Photo::class)->create();

        // Checks the database
        $this->assertDatabaseHas($conflictingModel->getTable(), $conflictingModel->attributesToArray());
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Update model
        $model->setAttribute('PHOTO_TITLE', $conflictingModel->getAttribute('PHOTO_TITLE'));

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => [
                    'PHOTO_TITLE' => $model->getAttribute('PHOTO_TITLE')
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('PATCH', route('photos.update', ['id' => $model->getKey()]), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiResponse409(
            [
                [
                    Members::ERROR_STATUS => '409',
                    Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                    Members::ERROR_DETAILS => "The given data was invalid.\n" .
                        $this->replaceAttribute(trans('validation.unique'), 'data.attributes.PHOTO_TITLE')
                ]
            ]
        );

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }
}
