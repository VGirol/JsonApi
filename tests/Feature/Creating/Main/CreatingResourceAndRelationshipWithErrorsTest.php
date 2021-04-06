<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Main;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class CreatingResourceAndRelationshipWithErrorsTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint
     * Relationship object is not valid
     * Should return 403 with errors
     *
     * @test
     */
    public function relationshipObjectIsNotValid()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'price' => [
                        Members::META => [
                            'key' => 'value'
                        ]
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => JsonApiStructureMessages::RELATIONSHIP_NO_DATA_MEMBER
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * POST /endpoint
     * Some related resources does not exist
     * Should return 404 with errors
     *
     * @test
     */
    public function someRelatedDoesNotExist()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(Tags::class, 3)->create();

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'tags' => [
                        Members::DATA => array_merge(
                            $related->map(
                                function ($item) {
                                    return [
                                        Members::TYPE => 'tags',
                                        Members::ID => strval($item->getKey())
                                    ];
                                }
                            )->toArray(),
                            [
                                [
                                    Members::TYPE => 'tags',
                                    Members::ID => '6666'
                                ]
                            ]
                        )
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
        $related->each(
            function ($item) use ($model) {
                $this->assertDatabaseMissing(
                    'pivot_phototags',
                    [
                        'PHOTO_ID' => $model->getKey(),
                        'TAGS_ID' => $item->getKey()
                    ]
                );
            }
        );

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse404([
            [
                Members::ERROR_STATUS => '404',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[404],
                Members::ERROR_DETAILS => sprintf(Messages::UPDATING_REQUEST_RELATED_NOT_FOUND, 'tags')
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
        $related->each(
            function ($item) use ($model) {
                $this->assertDatabaseMissing(
                    'pivot_phototags',
                    [
                        'PHOTO_ID' => $model->getKey(),
                        'TAGS_ID' => $item->getKey()
                    ]
                );
            }
        );
    }

    /**
     * POST /endpoint
     * Relationship does not exist
     * Should return 400 with errors
     *
     * @test
     */
    public function relationshipDoesNotExist()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(Tags::class)->create();

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => $model->attributesToArray(),
                Members::RELATIONSHIPS => [
                    'inexistent' => [
                        Members::DATA => [
                            Members::TYPE => 'tags',
                            Members::ID => strval($related->getKey())
                        ]
                    ]
                ]
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Check response
        $response->assertJsonApiResponse403([
            [
                Members::ERROR_STATUS => '403',
                Members::ERROR_TITLE => JsonResponse::$statusTexts[403],
                Members::ERROR_DETAILS => sprintf(Messages::NON_EXISTENT_RELATIONSHIP, 'inexistent')
            ]
        ]);

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }
}
