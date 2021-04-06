<?php

namespace VGirol\JsonApi\Tests\Feature\Updating\Related;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class UpdatingRelatedTest extends TestCase
{
    use CompleteSetUp;

    /**
     * PATCH /endpoint/{parentId}/{relationship}/{id}
     * Updating related resource with some fields that are automatically filled
     * Should return 200 with data array
     *
     * @test
     */
    public function updatingRelatedWithFieldModifiedByServer()
    {
        // Creates an object with filled out fields
        $parent = factory(Author::class)->create();
        $model = factory(Photo::class)->create([
            $parent->getKeyName() => $parent->getKey()
        ]);

        // Field PHOTO_DATE is set to null so that,
        // that field will be automatically filled when model is updated
        $model->setAttribute('PHOTO_DATE', null);
        $model->save();

        // Checks the database
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Update model
        $model->setAttribute('PHOTO_TITLE', 'new value');

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => [
                    'PHOTO_TITLE' => $model->PHOTO_TITLE
                ]
            ]
        ];

        // Sends request and gets response
        $url = route(
            'authors.related.update',
            [
                'parentId' => $parent->getKey(),
                'relationship' => 'photos',
                'id' => $model->getKey()
            ]
        );
        $response = $this->jsonApi('PATCH', $url, $content);

        // Some attributes are automatically filled when model is updated
        // Sets these attributes to expected values
        $model->setAttribute('PHOTO_DATE', '01-01-1970');

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated($expected->toArray());

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Checks the top-level links object
        $response->assertJsonApiDocumentLinksObjectEquals([
            Members::LINK_SELF => $url
        ]);
    }

    /**
     * PATCH /endpoint/{parentId}/{relationship}/{id}
     * Updating related resource
     * Should return 204 with no content
     *
     * @test
     */
    public function updatingResourceWithNoContent()
    {
        // Creates an object with filled out fields
        $parent = factory(Author::class)->create();
        $model = factory(Photo::class)->create([
            $parent->getKeyName() => $parent->getKey()
        ]);

        // Checks the database
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Update model
        $model->setAttribute('PHOTO_TITLE', 'new value');

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'PATCH',
            route(
                'authors.related.update',
                [
                    'parentId' => $parent->getKey(),
                    'relationship' => 'photos',
                    'id' => $model->getKey()
                ]
            ),
            $content
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
    }

    /**
     * PATCH /endpoint/{parentId}/{relationship}/{id}
     * Updating related resource
     * Should return 200 with document meta array
     *
     * @test
     */
    public function updatingResourceWithDocumentMeta()
    {
        // Creates an object with filled out fields
        $model = factory(Author::class)->create();
        $parent = factory(Photo::class)->create([
            $model->getKeyName() => $model->getKey()
        ]);

        // Checks the database
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Update model
        $model->setAttribute('AUTHOR_NAME', 'John Doe');

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => 'author',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => [
                    'AUTHOR_NAME' => $model->AUTHOR_NAME
                ]
            ]
        ];

        // Sends request and gets response
        $response = $this->jsonApi(
            'PATCH',
            route(
                'photos.related.update',
                [
                    'parentId' => $parent->getKey(),
                    'relationship' => 'author',
                    'id' => $model->getKey()
                ]
            ),
            $content
        );

        // Creates the expected resource
        $expected = (new HelperFactory())->document()
            ->addToMeta('writes', 'best-sellers');

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiUpdated(null);

        $json = $response->json();
        Assert::assertHasMeta($json);
        PHPUnit::assertEquals($expected->toArray(), $json);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
    }
}
