<?php

namespace VGirol\JsonApi\Tests\Feature\Deleting\Related;

use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;

class DeletingRelatedTest extends TestCase
{
    use CompleteSetUp;

    /**
     * DELETE /endpoint/{id}
     * Deletes related resource (to-one) and returns response with no content
     * Should return 204
     *
     * @test
     */
    public function destroyToOneRelated()
    {
        // Creates an object with filled out fields
        $model = factory(Author::class)->create();
        $related = factory(Photo::class)->make();
        $model->photos()->save($related);

        $related->setAttribute($model->getKeyName(), $model->getKey());

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'DELETE',
            route(
                'authors.related.destroy',
                [
                    'parentId' => $model->getKey(),
                    'relationship' => 'photos',
                    'id' => $related->getKey()
                ]
            )
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }

    /**
     * DELETE /endpoint/{id}
     * Deletes related resource and returns response with content
     * Should return 200 and meta member
     *
     * @test
     */
    public function destroyAndResponseWithContent()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $model->author()->associate($related);

        $model->save();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'DELETE',
            route(
                'photos.related.destroy',
                [
                    'parentId' => $model->getKey(),
                    'relationship' => 'author',
                    'id' => $related->getKey()
                ]
            )
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiDeleted(
            [
                'message' => sprintf(ResourceObject::DELETED_MESSAGE, $related->getKey())
            ]
        );

        // Checks the database
        $model->setAttribute($related->getKeyName(), null);
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
    }

    /**
     * DELETE /endpoint/{id}
     * Deletes related resource (to-many) and its relationship to parent
     * and returns response with no content
     * Should return 204
     *
     * @test
     */
    public function destroyToManyRelated()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $related = factory(Tags:: class)->create();
        $model->tags()->attach($related);

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas(
            $model->tags()->getTable(),
            [
                $model->getKeyName() => $model->getKey(),
                $related->getKeyName() => $related->getKey()
            ]
        );

        DB::statement('PRAGMA foreign_keys = on');

        // Sends request and gets response
        $response = $this->jsonApi(
            'DELETE',
            route(
                'photos.related.destroy',
                [
                    'parentId' => $model->getKey(),
                    'relationship' => 'tags',
                    'id' => $related->getKey()
                ]
            )
        );

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();

        // Checks the database
        $this->assertDatabaseHas($model->getTable(), $model->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseMissing(
            $model->tags()->getTable(),
            [
                $model->getKeyName() => $model->getKey(),
                $related->getKeyName() => $related->getKey()
            ]
        );
    }
}
