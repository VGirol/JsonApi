<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceIdentifier\Single;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceIdentifier;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;

class ResourceIdentifierToArrayTest extends TestCase
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
    public function exportEmptyResourceIdentifier()
    {
        // Creates a request
        $request = $this->createRequest('photos', 'GET');

        // Creates a resource
        $resource = ResourceIdentifier::make(null);

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        PHPUnit::assertNull($data);
    }

    /**
     * @test
     */
    public function exportResourceIdentifier()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Creates a request
        $request = $this->createRequest("photos/{$model->getKey()}", 'GET');

        // Creates a resource
        $resource = ResourceIdentifier::make($model);

        // Creates expected result
        $expected = (new HelperFactory())->resourceIdentifier($model, 'photo')
            ->toArray();

        // Exports model
        $data = $resource->toArray($request);

        // Executes all the tests
        $strict = config('jsonapi.disallowUnsafeCharacters');

        Assert::assertIsValidResourceIdentifierObject($data, $strict);
        Assert::assertResourceIdentifierEquals($expected, $data);
    }
}
