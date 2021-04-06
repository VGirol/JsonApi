<?php

namespace VGirol\JsonApi\Tests\Unit\Services\RelationshipService;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\PhpunitException\SetExceptionsTrait;

class HasManyTest extends TestCase
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
    public function createHasManyWithCollectionOfRelated()
    {
        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $related = factory(Photo::class, $count)->make();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseMissing($item->getTable(), $item->attributesToArray());
        }

        // Launch method to test
        $service->create($parent->photos(), $related);

        // Assert $related is attached to $parent
        PHPUnit::assertCount($count, $parent->photos()->get());
        foreach ($related as $item) {
            $item->setAttribute($parent->getKeyName(), $parent->getKey());
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
    }

    /**
     * @test
     */
    public function createHasManyWithSingleRelated()
    {
        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $related = factory(Photo::class)->make();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Launch method to test
        $service->create($parent->photos(), $related);

        // Assert $related is attached to $parent
        PHPUnit::assertCount(1, $parent->photos);
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
    }

    /**
     * @test
     */
    public function updateHasMany()
    {
        // Set config
        config()->set('jsonapi.relationshipFullReplacementIsAllowed', true);

        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $old = factory(Photo::class, $count)->create();
        $related = factory(Photo::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $old to $parent
        $service->create($parent->photos(), $old);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($old as $item) {
            $item->setAttribute($parent->getKeyName(), $parent->getKey());
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount($count, $parent->photos()->get());

        // Launch method to test
        $service->update($parent->photos(), $related);

        // Assert $old is allways stored in DB and $related is now attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($old as $item) {
            $item->setAttribute($parent->getKeyName(), null);
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        $related->setAttribute($parent->getKeyName(), $parent->getKey());
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        PHPUnit::assertCount(1, $parent->photos()->get());
        PHPUnit::assertTrue($related->is($parent->photos()->getResults()->first()));
    }

    /**
     * @test
     */
    public function updateHasManyNotAllowed()
    {
        // Set config
        config()->set('jsonapi.relationshipFullReplacementIsAllowed', false);

        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $old = factory(Photo::class, $count)->create();
        $related = factory(Photo::class)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $old to $parent
        $service->create($parent->photos(), $old);

        // Set expected exception
        $this->setFailure(JsonApi403Exception::class, Messages::RELATIONSHIP_FULL_REPLACEMENT);

        // Launch method to test
        $service->update($parent->photos(), $related);
    }

    /**
     * @test
     */
    public function clearHasMany()
    {
        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $related = factory(Photo::class, $count)->create();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->photos(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $item->setAttribute($parent->getKeyName(), $parent->getKey());
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->photos()->get());

        // Launch method to test
        $service->clear($parent->photos());

        // Assert $related is no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $item->setAttribute($parent->getKeyName(), null);
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount(0, $parent->photos()->get());
    }

    /**
     * @test
     */
    public function removeHasManySingle()
    {
        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $related = factory(Photo::class, $count)->create();
        $removed = $related->first();

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->photos(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $item->setAttribute($parent->getKeyName(), $parent->getKey());
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->photos()->get());

        // Launch method to test
        $service->remove($parent->photos(), $removed);

        // Assert $removed is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count - 1, $parent->photos()->get());
        PHPUnit::assertFalse($parent->photos()->get()->contains($removed));
    }

    /**
     * @test
     */
    public function removeBelongsToManyCollection()
    {
        // Create objects with filled out fields
        $parent = factory(Author::class)->create();
        $count = 3;
        $related = factory(Photo::class, $count)->create();
        $countRemoved = 3;
        $removed = $related->slice(0, $countRemoved);

        // Get an instance of service
        $service = new RelationshipService(new FetchService());

        // Attach $related to $parent
        $service->create($parent->photos(), $related);

        // Check initial state
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $item->setAttribute($parent->getKeyName(), $parent->getKey());
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count, $parent->photos()->get());

        // Launch method to test
        $service->remove($parent->photos(), $removed);

        // Assert $removed is allways stored in DB but no more attached to $parent
        $this->assertDatabaseHas($parent->getTable(), $parent->attributesToArray());
        foreach ($related as $item) {
            $this->assertDatabaseHas($item->getTable(), $item->attributesToArray());
        }
        PHPUnit::assertCount($count - $countRemoved, $parent->photos()->get());
        foreach ($removed as $item) {
            PHPUnit::assertFalse($parent->photos()->get()->contains($item));
        }
    }

    // /**
    //  * @test
    //  */
    // public function removeBelongsToManyModelThatIsNotAttachedToParent()
    // {
    //     // Create objects with filled out fields
    //     $parent = factory(Photo::class)->create();
    //     $count = 5;
    //     $related = factory(Tags::class, $count)->create();

    //     $not = factory(Tags::class)->create();

    //     // Get an instance of service
    //     $service = new RelationshipService(new FetchService());

    //     // Attach $related to $parent
    //     $service->create($parent->tags(), $related);

    //     // Check initial state
    //     $this->assertDatabaseHas('photo', $parent->attributesToArray());
    //     foreach ($related as $item) {
    //         $this->assertDatabaseHas('tags', $item->attributesToArray());
    //     }
    //     PHPUnit::assertCount($count, $parent->tags()->get());
    //     $this->assertDatabaseHas('tags', $not->attributesToArray());

    //     // Launch method to test
    //     $service->remove($parent->tags(), $not);

    //     // Assert $related is allways stored in DB and attached to $parent
    //     $this->assertDatabaseHas('photo', $parent->attributesToArray());
    //     foreach ($related as $item) {
    //         $this->assertDatabaseHas('tags', $item->attributesToArray());
    //     }
    //     PHPUnit::assertCount($count, $parent->tags()->get());
    //     $this->assertDatabaseHas('tags', $not->attributesToArray());
    // }
}
