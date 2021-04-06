<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Single;

use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Comment;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;

class ResourceObjectToArrayWithRelationshipsTest extends TestCase
{
    use CanCreateRequest;
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpToolsAliases(true);
        $this->setUpToolsRoutes();
        $this->setUpToolsModels();
    }

    /**
     * @test
     */
    public function exportResourceObjectWithEmptyToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = null;
        $model->setRelation('price', $related);

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET', ['include' => 'price']);

        // Creates a resource
        $resource = ResourceObject::make($model, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->loadRelationship('price', 'price')
            ->toArray();

        // Export model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertHasRelationships($data);
        Assert::assertResourceObjectEquals($expected, $data);
    }

    /**
     * @test
     */
    public function exportResourceObjectWithToOneRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(Price::class)->make([
            'PHOTO_ID' => $model->getKey(),
        ]);
        $model->setRelation('price', $related);

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET', ['include' => 'price']);

        // Creates a resource
        $resource = ResourceObject::make($model, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->loadRelationship('price', 'price')
            ->toArray();

        // Export model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertHasRelationships($data);
        Assert::assertResourceObjectEquals($expected, $data);
    }

    /**
     * @test
     */
    public function exportResourceObjectWithEmptyToManyRelationships()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = collect([]);
        $model->setRelation('comments', $related);

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET', ['include' => 'comments']);

        // Creates a resource
        $resource = ResourceObject::make($model, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->loadRelationship('comments', 'comment')
            ->toArray();

        // Export model as resource object
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertHasRelationships($data);
        Assert::assertResourceObjectEquals($expected, $data);
    }

    /**
     * @test
     */
    public function resourceObjectWithToManyRelationships()
    {
        $count = 5;

        // Set config
        config()->set('jsonapi.include.allowed', true);
        config()->set('jsonapi.pagination.allowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();
        $related = factory(
            Comment::class,
            $count
        )->make([
            'PHOTO_ID' => $model->getKey(),
        ]);
        $model->setRelation('comments', $related);

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET', ['include' => 'comments']);

        // Creates a resource
        $resource = ResourceObject::make($model, jsonapiInclude($request)->parameters());

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->loadRelationship('comments', 'comment')
            ->toArray();

        // Export model as resource object
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertHasRelationships($data);
        Assert::assertResourceObjectEquals($expected, $data);
    }
}
