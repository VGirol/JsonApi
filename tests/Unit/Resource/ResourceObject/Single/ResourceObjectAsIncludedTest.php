<?php

namespace VGirol\JsonApi\Tests\Unit\Resource\ResourceObject\Single;

use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class ResourceObjectAsIncludedTest extends TestCase
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
    public function exportResourceObjectAsIncluded()
    {
        // Set config
        config()->set('jsonapi.include.allowed', true);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->make();

        // Creates a request
        $request = $this->createRequest("author", 'GET');

        // Creates a resource
        $resource = ResourceObject::make($model);

        $meta = ['test' => 'anything'];
        $resource->additional([Members::META => $meta]);

        // Creates expected result
        $expected = (new HelperFactory())->resourceObject($model, 'photo', 'photos')
            ->addSelfLink()
            ->setMeta($meta)
            ->toArray();

        // Exports model
        $data = $resource->asIncluded($request);

        // Executes all the tests
        $strict = true;

        Assert::assertIsValidResourceObject($data, $strict);
        Assert::assertResourceObjectEquals($expected, $data);
    }
}
