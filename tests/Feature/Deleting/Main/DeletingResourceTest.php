<?php

namespace VGirol\JsonApi\Tests\Feature\Deleting\Main;

use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;

class DeletingResourceTest extends TestCase
{
    use CompleteSetUp;

    /**
     * DELETE /endpoint/{id}
     * Deletes existing model and returns response with no content
     * Should return 204
     *
     * @test
     */
    public function destroyAndResponseWithNoContent()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('DELETE', route('photos.destroy', ['id' => $model->getKey()]));

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }

    /**
     * DELETE /endpoint/{id}
     * Deletes existing model and returns response with content
     * Should return 200 and meta member
     *
     * @test
     */
    public function destroyAndResponseWithContent()
    {
        // Creates an object with filled out fields
        $model = factory(Author::class)->create();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi('DELETE', route('authors.destroy', ['id' => $model->getKey()]));

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiDeleted(
            [
                'message' => sprintf(ResourceObject::DELETED_MESSAGE, $model->getKey())
            ]
        );

        // Checks the database
        $this->assertDatabaseMissing($model->getTable(), $model->attributesToArray());
    }
}
