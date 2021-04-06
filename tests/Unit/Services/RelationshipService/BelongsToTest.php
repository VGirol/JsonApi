<?php

namespace VGirol\JsonApi\Tests\Unit\Services\RelationshipService;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;

class BelongsToTest extends TestCase
{
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
        $this->setUpToolsModels();
        $this->setUpToolsDB();
    }

    /**
     * @test
     */
    public function createBelongsTo()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $parent->setAttribute($related->getKeyName(), null);
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->author()->get());

        // Launch method to test
        $service->create($parent->author(), $related);

        // Assert $related is attached to $parent
        $parent->setAttribute($related->getKeyName(), $related->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        PHPUnit::assertCount(1, $parent->author()->get());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));
    }

    /**
     * @test
     */
    public function updateBelongsTo()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $old = factory(Author::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $old to $parent
        $service->create($parent->author(), $old);

        // Check initial state
        $parent->setAttribute($old->getKeyName(), $old->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($old->is($parent->author()->getResults()));

        // Launch method to test
        $service->update($parent->author(), $related);

        // Assert $old is allways stored in DB and $related is now attached to $parent
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $parent->setAttribute($related->getKeyName(), $related->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));
    }

    /**
     * @test
     */
    public function clearBelongsTo()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->author(), $related);

        // Check initial state
        $parent->setAttribute($related->getKeyName(), $related->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));

        // Launch method to test
        $service->clear($parent->author());

        // Assert $related is no more attached to $parent
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $parent->setAttribute($related->getKeyName(), null);
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        PHPUnit::assertCount(0, $parent->author()->get());
    }

    /**
     * @test
     */
    public function removeBelongsTo()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->author(), $related);

        // Check initial state
        $parent->setAttribute($related->getKeyName(), $related->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));

        // Launch method to test
        $service->remove($parent->author(), $related);

        // Assert $related is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $parent->setAttribute($related->getKeyName(), null);
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());

        // Assert relationship is empty
        PHPUnit::assertCount(0, $parent->author()->get());
    }

    /**
     * @test
     */
    public function removeBelongsToModelThatIsNotAttachedToParent()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        $not = factory(Author::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->author(), $related);

        // Check initial state
        $parent->setAttribute($related->getKeyName(), $related->getKey());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));

        // Launch method to test
        $service->remove($parent->author(), $not);

        // Assert $related is allways stored in DB and attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->author()->getResults()));
    }

    /**
     * @test
     */
    public function removeBelongsToRelationIsEmpty()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Author::class)->create();
        $parent->makeVisible($related->getKeyName());

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->author()->get());

        // Launch method to test
        $service->remove($parent->author(), $related);

        // Assert $related is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());

        // Assert relationship is empty
        PHPUnit::assertCount(0, $parent->author()->get());
    }
}
