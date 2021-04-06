<?php

namespace VGirol\JsonApi\Tests\Unit\Services\RelationshipService;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\PhpunitException\SetExceptionsTrait;

class BelongsToManyTest extends TestCase
{
    use UsesTools;
    use SetExceptionsTrait;

    /**
     * Setup before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpToolsAliases(true);
        $this->setUpToolsModels();
        $this->setUpToolsDB();
    }

    /**
     * @test
     */
    public function createBelongsToMany()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 3;
        $related = factory(Tags::class, $count)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount(0, $parent->tags()->get());

        // Launch method to test
        $service->create($parent->tags(), $related);

        // Assert $related is attached to $parent
        PHPUnit::assertCount($count, $parent->tags()->get());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
            PHPUnit::assertTrue($parent->tags()->get()->contains($item));
        }
    }

    /**
     * @test
     */
    public function updateBelongsTo()
    {
        // Set config
        config()->set('jsonapi.relationshipFullReplacementIsAllowed', true);

        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 3;
        $old = factory(Tags::class, $count)->create();
        $related = factory(Tags::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $old to $parent
        $service->create($parent->tags(), $old);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($old as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount($count, $parent->tags()->get());

        // Launch method to test
        $service->update($parent->tags(), $related);

        // Assert $old is allways stored in DB and $related is now attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($old as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(1, $parent->tags()->get());
        PHPUnit::assertTrue($related->is($parent->tags()->getResults()->first()));
    }

    /**
     * @test
     */
    public function updateBelongsToNotAllowed()
    {
        // Set config
        config()->set('jsonapi.relationshipFullReplacementIsAllowed', false);

        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 3;
        $old = factory(Tags::class, $count)->create();
        $related = factory(Tags::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $old to $parent
        $service->create($parent->tags(), $old);

        // Set expected exception
        $this->setFailure(JsonApi403Exception::class, Messages::RELATIONSHIP_FULL_REPLACEMENT);

        // Launch method to test
        $service->update($parent->tags(), $related);
    }

    /**
     * @test
     */
    public function clearBelongsToMany()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 3;
        $related = factory(Tags::class, $count)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->tags(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->tags()->get());

        // Launch method to test
        $service->clear($parent->tags());

        // Assert $related is no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount(0, $parent->tags()->get());
    }

    /**
     * @test
     */
    public function removeBelongsToManySingle()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 3;
        $related = factory(Tags::class, $count)->create();
        $removed = $related->first();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->tags(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->tags()->get());

        // Launch method to test
        $service->remove($parent->tags(), $removed);

        // Assert $removed is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count - 1, $parent->tags()->get());
        PHPUnit::assertFalse($parent->tags()->get()->contains($removed));
    }

    /**
     * @test
     */
    public function removeBelongsToManyCollection()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 5;
        $related = factory(Tags::class, $count)->create();
        $countRemoved = 3;
        $removed = $related->slice(0, $countRemoved);

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->tags(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->tags()->get());

        // Launch method to test
        $service->remove($parent->tags(), $removed);

        // Assert $removed is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count - $countRemoved, $parent->tags()->get());
        foreach ($removed as $item) {
            PHPUnit::assertFalse($parent->tags()->get()->contains($item));
        }
    }

    /**
     * @test
     */
    public function removeBelongsToManyModelThatIsNotAttachedToParent()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $count = 5;
        $related = factory(Tags::class, $count)->create();

        $not = factory(Tags::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->tags(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->tags()->get());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());

        // Launch method to test
        $service->remove($parent->tags(), $not);

        // Assert $related is allways stored in DB and attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->tags()->get());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());
    }
}
