<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Main;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiAssert\Laravel\Assert as AssertLaravel;
use VGirol\JsonApiConstant\Members;

class CreatingResourceTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint
     * Creating resource (without relationships)
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingResource()
    {
        // Sets config
        config()->set('jsonapi.creationAddLocationHeader', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Creates the expected resource
        $self = route('photos.show', ['id' => $model->getKey()]);
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', $self);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Checks the top-level links object
        AssertLaravel::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource (without relationships)
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingResourceWithoutLocationHeader()
    {
        // Sets config
        config()->set('jsonapi.creationAddLocationHeader', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', 1);

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeaderMissing('Location');

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Checks the top-level links object
        AssertLaravel::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource (without relationships) with client-generated ID
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingResourceWithClientGeneratedId()
    {
        // Sets config
        config()->set('jsonapi.creationAddLocationHeader', false);
        config()->set('jsonapi.clientGeneratedIdIsAllowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', 456);
        $model->setAttribute('PHOTO_DATE', null);

        $resourceType = 'photo';

        // Creates content of the request
        $attributes = $model->attributesToArray();
        unset($attributes['PHOTO_DATE']);
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $attributes
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Creates the expected resource
        $model->setAttribute('PHOTO_DATE', '01-01-1970');
        $expected = (new HelperFactory())->resourceObject($model, $resourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeaderMissing('Location');

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Checks the top-level links object
        AssertLaravel::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint
     * Creating resource (without relationships) with client-generated ID
     * Should return 204
     *
     * @test
     */
    public function creatingResourceWithClientGeneratedIdReturns204()
    {
        // Sets config
        config()->set('jsonapi.creationAddLocationHeader', false);
        config()->set('jsonapi.clientGeneratedIdIsAllowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $model->setAttribute('PHOTO_ID', 456);

        $resourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $resourceType,
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('POST', route('photos.store'), $content);

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();
        $response->assertHeaderMissing('Location');

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
    }
}
