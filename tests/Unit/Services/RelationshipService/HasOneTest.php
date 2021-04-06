<?php

namespace VGirol\JsonApi\Tests\Unit\Services\RelationshipService;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;

class HasOneTest extends TestCase
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
    public function createHasOneWithModel()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->make();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->price()->get());

        // Launch method to test
        $service->create($parent->price(), $related);

        // Assert $related is stored in DB and attached to $parent
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(1, $parent->price()->get());
    }

    /**
     * @test
     */
    public function createHasOneWithJson()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $related->setAttribute($parent->getKeyName(), null);
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->price()->get());

        // Launch method to test
        $json = [
            Members::TYPE => 'price',
            Members::ID => $related->getKey()
        ];
        $service->create($parent->price(), $json);

        // Assert $related is stored in DB and attached to $parent
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        PHPUnit::assertCount(1, $parent->price()->get());
    }

    /**
     * @test
     */
    public function updateHasOne()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $old = factory(Price::class)->make();
        $related = factory(Price::class)->make();

        // Attach $old to $parent
        $parent->price()->save($old);

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $old->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($old->is($parent->price()->getResults()));

        // Launch method to test
        $service->update($parent->price(), $related);

        // Assert $related is stored in DB and attached to $parent
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertTrue($related->is($parent->price()->getResults()));

        // Assert $old is detached from $parent
        $old->setAttribute($parent->getKeyName(), null);
        $this->assertDatabaseHas($old->getTable(), $old->attributesToArray());
    }

    /**
     * @test
     */
    public function clearHasOne()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->make();

        // Attach $related to $parent
        $parent->price()->save($related);

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(1, $parent->price()->get());

        // Launch method to test
        $service->clear($parent->price());

        // Assert $related is allways stored in DB but no more attached to $parent
        $related->setAttribute($parent->getKeyName(), null);
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Assert relationship is empty
        PHPUnit::assertCount(0, $parent->price()->get());
    }

    /**
     * @test
     */
    public function removeHasOne()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->make();

        // Attach $related to $parent
        $parent->price()->save($related);

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(1, $parent->price()->get());

        // Launch method to test
        $service->remove($parent->price(), $related);

        // Assert $related is allways stored in DB but no more attached to $parent
        $related->setAttribute($parent->getKeyName(), null);
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Assert relationship is empty
        PHPUnit::assertCount(0, $parent->price()->get());
    }

    /**
     * @test
     */
    public function removeHasOneModelThatIsNotAttachedToParent()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->create();

        // Attach $related to $parent
        $parent->price()->save($related);

        // Create an object with filled out fields
        $not = factory(Price::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());

        // Launch method to test
        $service->remove($parent->price(), $not);

        // Assert nothing has changed
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas($not->getTable(), $not->attributesToArray());

        PHPUnit::assertCount(1, $parent->price()->get());
        PHPUnit::assertTrue($related->is($parent->price()->getResults()));
    }

    /**
     * @test
     */
    public function removeHasOneRelationIsEmpty()
    {
        // Create objects with filled out fields
        $parent = factory(Photo::class)->create();
        $related = factory(Price::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->price()->get());

        // Launch method to test
        $service->remove($parent->price(), $related);

        // Assert nothing has changed
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(0, $parent->price()->get());
    }
}
