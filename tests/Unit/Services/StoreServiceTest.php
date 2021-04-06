<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Exceptions\JsonApiDuplicateEntryException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Services\StoreService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;
use VGirol\PhpunitException\SetExceptionsTrait;

class StoreServiceTest extends TestCase
{
    use SetExceptionsTrait;
    use UsesTools;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTools(true);
    }

    /**
     * @test
     */
    public function saveModel()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $attributes = Arr::except($model->attributesToArray(), $model->getKeyName());
        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => $attributes
        ];
        $routeKey = 'photos';

        $this->assertDatabaseMissing('photo', $attributes);

        $result = $service->saveModel($data, $routeKey);

        $expectedAttributes = array_merge($attributes, [$model->getKeyName() => 1]);

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($expectedAttributes, $result->attributesToArray());

        $this->assertDatabaseHas('photo', $expectedAttributes);
    }

    /**
     * @test
     */
    public function saveModelWithAttributesToLower()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $attributes = Arr::except($model->attributesToArray(), $model->getKeyName());
        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => array_change_key_case($attributes, CASE_LOWER)
        ];
        $routeKey = 'photos';

        $this->assertDatabaseMissing('photo', $attributes);

        $result = $service->saveModel($data, $routeKey);

        $expectedAttributes = array_merge($attributes, [$model->getKeyName() => 1]);

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($expectedAttributes, $result->attributesToArray());

        $this->assertDatabaseHas('photo', $expectedAttributes);
    }

    /**
     * @test
     */
    public function saveModelWithClientGeneratedId()
    {
        config()->set('jsonapi.clientGeneratedIdIsAllowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $attributes = $model->attributesToArray();
        $data = [
            Members::TYPE => 'photo',
            Members::ID => strval($model->getKey()),
            Members::ATTRIBUTES => $attributes
        ];
        $routeKey = 'photos';

        $this->assertDatabaseMissing('photo', $attributes);

        $result = $service->saveModel($data, $routeKey);

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertNotEquals(1, $result->getKey());
        PHPUnit::assertEquals($attributes, $result->attributesToArray());

        $this->assertDatabaseHas('photo', $attributes);
    }

    /**
     * @test
     */
    public function saveModelWithClientGeneratedIdThrowsException()
    {
        config()->set('jsonapi.clientGeneratedIdIsAllowed', false);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $attributes = $model->attributesToArray();
        $data = [
            Members::TYPE => 'photo',
            Members::ID => strval($model->getKey()),
            Members::ATTRIBUTES => $attributes
        ];
        $routeKey = 'photos';

        $this->setFailure(JsonApi403Exception::class, Messages::CLIENT_GENERATED_ID_NOT_ALLOWED);

        $service->saveModel($data, $routeKey);

        $this->assertDatabaseMissing('photo', $attributes);
    }

    /**
     * @test
     */
    public function saveModelThrowsExceptionBecauseOfDuplicateEntry()
    {
        config()->set('jsonapi.clientGeneratedIdIsAllowed', false);

        // Creates an object with filled out fields
        $old = factory(Photo::class)->create();
        $model = factory(Photo::class)->make([$old->getKeyName() => $old->getKey()]);

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $attributes = $model->attributesToArray();
        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => $attributes
        ];
        $routeKey = 'photos';

        $this->setFailure(JsonApiDuplicateEntryException::class, JsonApiDuplicateEntryException::MESSAGE, 0);

        $this->assertDatabaseHas('photo', $old->attributesToArray());

        $service->saveModel($data, $routeKey);

        $this->assertDatabaseMissing('photo', $attributes);
    }

    /**
     * @test
     */
    public function updateModel()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $data = [
            Members::TYPE => 'photo',
            Members::ATTRIBUTES => [
                'PHOTO_TITLE' => 'New title'
            ]
        ];
        $routeKey = 'photos';

        $this->assertDatabaseHas('photo', $model->attributesToArray());

        $service->updateModel($data, $model->getKey(), $routeKey);

        $model->setAttribute('PHOTO_TITLE', 'New title');
        $attributes = $model->attributesToArray();

        $this->assertDatabaseHas('photo', $attributes);
    }

    /**
     * @test
     */
    public function deleteModel()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Get an instance of service
        $service = new StoreService(new AliasesService());

        $routeKey = 'photos';

        $this->assertDatabaseHas('photo', $model->attributesToArray());

        $service->deleteModel($routeKey, $model->getKey());

        $this->assertDatabaseMissing('photo', $model->attributesToArray());
    }
}
