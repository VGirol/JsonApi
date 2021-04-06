<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Main;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Exceptions\JsonApiDuplicateEntryException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class CreatingResourceWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint
     * Creating resource with invalid payload
     * Should return 403 with errors
     *
     * @test
     * @dataProvider creatingResourceWithInvalidPayloadProvider
     */
    public function creatingResourceWithInvalidPayload($content, $failureMsg)
    {
        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

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

    public function creatingResourceWithInvalidPayloadProvider()
    {
        return [
            'no data' => [
                [
                    Members::META => [
                        'key' => 'value'
                    ]
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
                        Members::META => 'test'
                    ]
                ],
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT
            ]
        ];
    }

    /**
     * POST /endpoint
     * Creating resource with invalid payload (invalid resource type)
     * Should return 409
     *
     * @test
     */
    public function creatingResourceWithInvalidTypeOfResourceObject()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', 1);

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'badType',
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse409([
            [
                Members::ERROR_STATUS => '409',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                Members::ERROR_DETAILS => "The given data was invalid.\nThe selected data.type is invalid."
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * POST /endpoint
     * Creating resource with client-generated ID and invalid payload
     * Should return 403
     *
     * @test
     */
    public function creatingResourceWithInvalidPayloadFieldRequired()
    {
        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => []
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => "The given data was invalid.\n" .
                    'The ' . $this->formatAttribute('data.attributes.PHOTO_TITLE') . ' field is required.'
            ]
        ]);
    }

    /**
     * POST /endpoint
     * Creating resource with client-generated ID although not allowed
     * Should return 403
     *
     * @test
     */
    public function creatingResourceWithClientGeneratedIdNotAllowed()
    {
        // Sets config
        config()->set('jsonapi.clientGeneratedIdIsAllowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', 456);

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => Messages::CLIENT_GENERATED_ID_NOT_ALLOWED
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * POST /endpoint
     * Creating resource with client-generated ID and invalid payload (conflicting ID)
     * Should return 409
     *
     * @test
     */
    public function creatingResourceWithConflictingClientGeneratedId()
    {
        // Sets config
        config([
            'jsonapi.clientGeneratedIdIsAllowed' => true
        ]);

        // Creates an object with filled out fields
        $oldModel = factory(Photo::class)->create();

        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', $oldModel->getKey());

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse409([
            [
                Members::ERROR_STATUS => '409',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                Members::ERROR_DETAILS => JsonApiDuplicateEntryException::MESSAGE
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * POST /endpoint
     * Creating resource with client-generated ID and invalid payload
     * Should return 403
     *
     * @test
     */
    public function creatingResourceWithClientGeneratedIdAndInvalidPayload()
    {
        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => '1',
                Members::ATTRIBUTES => []
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => "The given data was invalid.\n" .
                    'The ' . $this->formatAttribute('data.attributes.PHOTO_TITLE') . ' field is required.'
            ]
        ]);
    }

    /**
     * POST /endpoint
     * Creating resource with client-generated ID and invalid payload (conflicting data)
     * Should return 409
     *
     * @test
     */
    public function creatingResourceWithClientGeneratedIdAndConflictingData()
    {
        // Sets config
        config([
            'jsonapi.clientGeneratedIdIsAllowed' => true
        ]);

        // Creates an object with filled out fields
        $oldModel = factory(Photo::class)->create();

        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', $oldModel->getKey() + 1);
        $model->setAttribute('PHOTO_TITLE', $oldModel->getAttribute('PHOTO_TITLE'));

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse409([
            [
                Members::ERROR_STATUS => '409',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[409],
                Members::ERROR_DETAILS => "The given data was invalid.\n" .
                    $this->replaceAttribute(trans('validation.unique'), 'data.attributes.PHOTO_TITLE')
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }
}
