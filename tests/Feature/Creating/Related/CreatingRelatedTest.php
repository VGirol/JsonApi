<?php

namespace VGirol\JsonApi\Tests\Feature\Creating\Related;

use VGirol\JsonApi\Tests\Feature\CompleteSetUp;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApi\Tests\Tools\Models\Author;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApiAssert\Laravel\Assert;
use VGirol\JsonApiConstant\Members;

class CreatingRelatedTest extends TestCase
{
    use CompleteSetUp;

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Create single related resource and add it to a one-to-one relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingOneToOneRelated()
    {
        // Sets config
        config([
            'jsonapi.creationAddLocationHeader' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $related = factory(Price::class)->make([$model->getKeyName() => null]);

        $relatedResourceType = 'price';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('photos.related.store', ['parentId' => $model->getKey(), 'relationship' => $relatedResourceType]),
            $content
        );

        // Updates expected related
        $related->setAttribute('PHOTO_ID', $model->getKey());

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($related, $relatedResourceType, 'prices')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('prices.show', ['id' => $related->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Create single related resource and add it to a one-to-many relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingOneToManyRelated()
    {
        // Sets config
        config([
            'jsonapi.creationAddLocationHeader' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Author::class)->create();
        factory(Photo::class, 3)->create([$model->getKeyName() => $model->getKey()]);
        $related = factory(Photo::class)->make([$model->getKeyName() => null]);
        $related->makeVisible('AUTHOR_ID');

        $relatedResourceType = 'photo';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('authors.related.store', ['parentId' => $model->getKey(), 'relationship' => 'photos']),
            $content
        );

        // Updates expected related
        $related->setAttribute($model->getKeyName(), $model->getKey());

        // Creates the expected resource
        $related->makeHidden('AUTHOR_ID');
        $expected = (new HelperFactory())->resourceObject($related, $relatedResourceType, 'photos')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('photos.show', ['id' => $related->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Create single related resource and add it to a many-to-many relationship
     * Should return 201 with data array
     *
     * @test
     */
    public function creatingManyToManyRelated()
    {
        // Sets config
        config([
            'jsonapi.creationAddLocationHeader' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $tags = factory(Tags::class, 3)->create();
        $model->tags()->attach(
            $tags->random(rand(1, 2))->pluck('TAGS_ID')->toArray()
        );
        $related = factory(Tags::class)->make([$model->getKeyName() => null]);

        $relatedResourceType = 'tag';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('photos.related.store', ['parentId' => $model->getKey(), 'relationship' => 'tags']),
            $content
        );

        // Creates the expected resource
        $expected = (new HelperFactory())->resourceObject($related, $relatedResourceType, 'tags')
            ->addSelfLink()
            ->toArray();

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiCreated($expected);
        $response->assertHeader('Location', route('tags.show', ['id' => $related->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
        $this->assertDatabaseHas('pivot_phototags', ['PHOTO_ID' => $model->getKey(), 'TAGS_ID' => $related->getKey()]);

        // Checks the top-level links object
        Assert::assertNotHasMember(Members::LINKS, $response->json());
    }

    /**
     * POST /endpoint/{parentId}/{relationship}
     * Create single related resource and add it to a one-to-one relationship
     * Should return 204 with no content
     *
     * @test
     */
    public function creatingOneToOneRelatedWithClientGeneratedIdReturns204()
    {
        // Sets config
        config([
            'jsonapi.creationAddLocationHeader' => true,
            'jsonapi.clientGeneratedIdIsAllowed' => true
        ]);

        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $related = factory(Price::class)->make([$model->getKeyName() => $model->getKey()]);
        $related->setAttribute($related->getKeyName(), 456);

        $relatedResourceType = 'price';

        // Creates content of the request
        $content = [
            Members::DATA => [
                Members::TYPE => $relatedResourceType,
                Members::ID => strval($related->getKey()),
                Members::ATTRIBUTES => $related->attributesToArray()
            ]
        ];

        // Checks the database
        $this->assertDatabaseMissing($related->getTable(), $related->attributesToArray());

        // Sends request and gets response
        $response = $this->jsonApi(
            'POST',
            route('photos.related.store', ['parentId' => $model->getKey(), 'relationship' => $relatedResourceType]),
            $content
        );

        // Updates expected related
        $related->setAttribute($model->getKeyName(), $model->getKey());

        // Checks the response (status code, headers) and the content
        $response->assertJsonApiNoContent();
        $response->assertHeader('Location', route('prices.show', ['id' => $related->getKey()]));

        // Checks the database
        $this->assertDatabaseHas($related->getTable(), $related->attributesToArray());
    }
}
