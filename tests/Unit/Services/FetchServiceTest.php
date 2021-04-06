<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use DMS\PHPUnitExtensions\ArraySubset\Assert as ArraySubsetAssert;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use ReflectionClass;
use VGirol\JsonApi\Exceptions\JsonApi400Exception;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Exceptions\JsonApi404Exception;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Model\Related;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\Tools\Models\Photo;
use VGirol\JsonApi\Tests\Tools\Models\Price;
use VGirol\JsonApi\Tests\Tools\Models\Tags;
use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiConstant\Members;
use VGirol\PhpunitException\SetExceptionsTrait;

class FetchServiceTest extends TestCase
{
    use CanCreateRequest;
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
    public function find()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->find($request, Photo::class, $model->getKey());

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals(Photo::find($model->getKey()), $result);
    }

    /**
     * @test
     */
    public function findModelThatDoesNotExist()
    {
        // Creates an instance of request
        $request = $this->createRequest("/photos/1", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(ModelNotFoundException::class);

        $service->find($request, Photo::class, 1);
    }

    /**
     * @test
     */
    public function findModelWithFieldsQueryStringAutomaticallyAddKey()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $query = [
            'fields' => [
                'photo' => 'photo_title'
            ]
        ];
        $request = $this->createRequest("/photos/{$model->getKey()}", 'GET', $query);
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->find($request, Photo::class, $model->getKey());

        PHPUnit::assertInstanceOf(Photo::class, $result);

        $expected = $model->only(['PHOTO_ID', 'PHOTO_TITLE']);
        PHPUnit::assertEquals($expected, $result->toArray());
    }

    /**
     * @test
     */
    public function findModelWithQueryString()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $countTags = 3;
        $tags = factory(Tags::class, $countTags)->create();
        $model->tags()->attach(
            $tags->pluck('TAGS_ID')->toArray()
        );

        // Creates an instance of request
        $query = [
            'fields' => [
                'photo' => 'photo_title,photo_id'
            ],
            'include' => 'tags'
        ];
        $request = $this->createRequest("/photos/{$model->getKey()}", 'GET', $query);
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->find($request, Photo::class, $model->getKey());

        PHPUnit::assertInstanceOf(Photo::class, $result);

        $expected = $model->only(['PHOTO_ID', 'PHOTO_TITLE']);
        PHPUnit::assertEquals($expected, $result->attributesToArray());
        PHPUnit::assertTrue($result->relationLoaded('tags'));
        PHPUnit::assertEquals($countTags, $result->tags->count());

        PHPUnit::assertEquals(
            $model->tags->sortByDesc('TAGS_ID')->values()->toArray(),
            $result->tags->sortByDesc('TAGS_ID')->values()->toArray()
        );
    }

    /**
     * @test
     * @dataProvider findModelWithBadQueryStringProvider
     */
    public function findModelWithBadQueryString($query)
    {
        // Creates an instance of request
        $request = $this->createRequest("/photos/1", 'GET', $query);
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(JsonApiException::class, Messages::ERROR_FETCHING_SINGLE_WITH_NOT_ALLOWED_QUERY_PARAMETERS);

        $result = $service->find($request, Photo::class, 1);
    }

    public function findModelWithBadQueryStringProvider()
    {
        return [
            'sort' => [
                ['sort' => 'field1']
            ],
            'filter' => [
                ['filter' => ['field1' => 'value1', 'field2' => 'value2']]
            ],
            'pagination' => [
                ['page' => ['number' => 2, 'size' => 20]]
            ]
        ];
    }

    /**
     * @test
     */
    public function getCollectionWithoutPagination()
    {
        // Sets config
        config()->set('jsonapi.pagination.allowed', false);
        config()->set('jsonapi.pagination.routes', ['*.index']);

        // Creates an object with filled out fields
        $count = 15;
        $collection = factory(Photo::class, $count)->create();

        // Creates an instance of request
        $request = $this->createRequest('/photos', 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->get($request, Photo::class);

        PHPUnit::assertInstanceOf(EloquentCollection::class, $result);
        PHPUnit::assertEquals($count, $result->count());
        PHPUnit::assertObjectHasAttribute('itemTotal', $result);
        PHPUnit::assertEquals($count, $result->itemTotal);

        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('pagination');
        $property->setAccessible(true);
        $paginationService = $property->getValue($service);
        $reflection = new ReflectionClass($paginationService);
        $property = $reflection->getProperty('totalItem');
        $property->setAccessible(true);
        PHPUnit::assertEquals($count, $property->getValue($paginationService));

        foreach ($result as $item) {
            PHPUnit::assertInstanceOf(Photo::class, $item);
            PHPUnit::assertContains($item->getKey(), $collection->pluck('PHOTO_ID')->toArray());
        }
    }

    /**
     * @test
     */
    public function getCollectionWithSortQuery()
    {
        // Sets config
        config()->set('jsonapi.pagination.allowed', false);
        config()->set('jsonapi.pagination.routes', ['*.index']);

        // Creates an object with filled out fields
        factory(Photo::class, 2)->create();
        $count = 3;
        $collection = factory(Photo::class, $count)->create([
            'PHOTO_SIZE' => '123456'
        ]);

        // Creates an instance of request
        $request = $this->createRequest(
            '/photos',
            'GET',
            [
                'sort' => '-PHOTO_TITLE',
                'filter' => [
                    'PHOTO_SIZE' => '123456'
                ]
            ]
        );
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->get($request, Photo::class);

        PHPUnit::assertInstanceOf(EloquentCollection::class, $result);
        PHPUnit::assertEquals($count, $result->count());
        PHPUnit::assertObjectHasAttribute('itemTotal', $result);
        PHPUnit::assertEquals($count, $result->itemTotal);

        $expected = $collection->sortByDesc('PHOTO_TITLE')->values()->toArray();
        PHPUnit::assertEquals($expected, $result->toArray());
    }

    /**
     * @test
     */
    public function getCollectionWithPagination()
    {
        $count = 15;
        $defaultSize = 10;

        // Sets config
        config()->set('jsonapi.pagination.allowed', true);
        config()->set('jsonapi.pagination.routes', ['*.index']);
        config()->set('json-api-paginate.default_size', $defaultSize);

        // Creates an object with filled out fields
        $collection = factory(Photo::class, $count)->create();

        // Creates an instance of request
        $request = $this->createRequest('/photos', 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->get($request, Photo::class);

        PHPUnit::assertInstanceOf(EloquentCollection::class, $result);
        PHPUnit::assertEquals($defaultSize, $result->count());
        PHPUnit::assertObjectHasAttribute('itemTotal', $result);
        PHPUnit::assertEquals($count, $result->itemTotal);
        foreach ($result as $item) {
            PHPUnit::assertInstanceOf(Photo::class, $item);
            PHPUnit::assertContains($item->getKey(), $collection->pluck('PHOTO_ID')->toArray());
        }
    }

    /**
     * @test
     */
    public function getCollectionThrowsException()
    {
        // Creates an object with filled out fields
        $collection = factory(Photo::class, 3)->create();

        // Creates an instance of request
        $request = $this->createRequest('/photos', 'GET', ['sort' => 'badField']);
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(JsonApiException::class, sprintf(Messages::SORTING_BAD_FIELD, '+badField'));

        $service->get($request, Photo::class);
    }

    /**
     * @test
     */
    public function findRelated()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $countTags = 3;
        $tags = factory(Tags::class, $countTags)->create();
        $model->tags()->attach(
            $tags->pluck('TAGS_ID')->toArray()
        );
        $tag = $model->tags->random();

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/tags/{$tag->getKey()}", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->findRelated($request, $model->getKey(), 'tags', $tag->getKey());

        PHPUnit::assertInstanceOf(Tags::class, $result);

        ArraySubsetAssert::assertArraySubset($tag->attributesToArray(), $result->attributesToArray());
    }

    /**
     * @test
     */
    public function getRelatedOfToManyRelation()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $countTags = 3;
        $tags = factory(Tags::class, $countTags)->create();
        $model->tags()->attach(
            $tags->pluck('TAGS_ID')->toArray()
        );

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/tags", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelated($request, $model->getKey(), 'tags');

        PHPUnit::assertInstanceOf(EloquentCollection::class, $result);
        PHPUnit::assertEquals($countTags, $result->count());

        foreach ($result as $item) {
            PHPUnit::assertInstanceOf(Tags::class, $item);
            PHPUnit::assertContains($item->getKey(), $tags->pluck('TAGS_ID')->toArray());
        }
    }

    /**
     * @test
     */
    public function getRelatedOfToOneRelation()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $price = factory(Price::class)->create(['PHOTO_ID' => $model->getKey()]);

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/price", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelated($request, $model->getKey(), 'price');

        PHPUnit::assertInstanceOf(Price::class, $result);

        ArraySubsetAssert::assertArraySubset($price->attributesToArray(), $result->attributesToArray());
    }

    /**
     * @test
     */
    public function getRelatedOfEmptyToOneRelation()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/price", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelated($request, $model->getKey(), 'price');

        PHPUnit::assertNull($result);
    }

    /**
     * @test
     */
    public function getRelatedOfToOneRelationThrowsException()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        factory(Price::class)->create(['PHOTO_ID' => $model->getKey()]);

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/price", 'GET', ['sort' => 'PRICE_ID']);
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(JsonApi400Exception::class, Messages::SORTING_IMPOSSIBLE_FOR_TO_ONE_RELATIONSHIP);

        $service->getRelated($request, $model->getKey(), 'price');
    }

    /**
     * @test
     */
    public function getRelationFromModel()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelationFromModel($model, 'tags');

        PHPUnit::assertInstanceOf(Relation::class, $result);
    }

    /**
     * @test
     */
    public function getRelationFromModelThrowsException()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Get an instance of service
        $service = new FetchService();

        $relationship = 'inexistant';

        $this->setFailure(JsonApi403Exception::class, sprintf(Messages::NON_EXISTENT_RELATIONSHIP, $relationship));

        $service->getRelationFromModel($model, $relationship);
    }

    /**
     * @test
     */
    public function findParent()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/tags", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->findParent($request, $model->getKey());

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($model->getKey(), $result->getKey());
    }

    /**
     * @test
     */
    public function findParentThrowsException()
    {
        // Creates an instance of request
        $request = $this->createRequest("/photos/1/tags", 'GET');
        $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(ModelNotFoundException::class);

        $service->findParent($request, 1);
    }

    /**
     * @test
     */
    public function getRequiredModelWithPostMethodAndNoAutomaticChanges()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => $model->attributesToArray()
            ]
        ];

        $request = $this->createRequest('/photos', 'POST', $content);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRequiredModel($request, $model->getKey(), $model);

        $expected = Photo::find($model->getKey());
        $expected->automaticChanges = false;
        $expected->makeHidden('automaticChanges');

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($model->getKey(), $result->getKey());
        PHPUnit::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getRequiredModelWithPostMethodAndAutomaticChanges()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ATTRIBUTES => array_diff_key(
                    $model->attributesToArray(),
                    ['PHOTO_DATE' => true]
                )
            ]
        ];

        $request = $this->createRequest('/photos', 'POST', $content);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRequiredModel($request, $model->getKey(), $model);

        $expected = Photo::find($model->getKey());
        $expected->automaticChanges = true;
        $expected->makeHidden('automaticChanges');

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($model->getKey(), $result->getKey());
        PHPUnit::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getRequiredModelWithPatchMethodAndAutomaticChanges()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();
        $model->setAttribute('PHOTO_DATE', null);
        $model->save();

        // Creates an instance of request
        $content = [
            Members::DATA => [
                Members::TYPE => 'photo',
                Members::ID => strval($model->getKey()),
                Members::ATTRIBUTES => ['PHOTO_TITLE' => $model->PHOTO_TITLE]
            ]
        ];
        $request = $this->createRequest("/photos/{$model->getKey()}", 'PATCH', $content);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRequiredModel($request, $model->getKey(), $model);

        $expected = Photo::find($model->getKey());
        $expected->automaticChanges = true;
        $expected->makeHidden('automaticChanges');

        PHPUnit::assertInstanceOf(Photo::class, $result);
        PHPUnit::assertEquals($model->getKey(), $result->getKey());
        PHPUnit::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getRelationFromRequest()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        // Creates an instance of request
        $request = $this->createRequest("/photos/{$model->getKey()}/tags", 'GET');
        // $this->swap('request', $request);

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelationFromRequest($request, $model->getKey(), 'tags');

        PHPUnit::assertInstanceOf(Relation::class, $result);
    }

    /**
     * @test
     * @dataProvider extractRelatedProvider
     */
    public function extractRelated($type, $child)
    {
        // Creates an object with filled out fields
        $count = 5;
        $tags = factory(Tags::class, $count)->create();

        // Creates data
        switch ($type) {
            case 'array':
                $data = $tags->map(function ($item) use ($child) {
                    return $this->getChild($item, $child);
                })->all();
                break;
            case 'collection':
                $data = $tags->map(function ($item) use ($child) {
                    return $this->getChild($item, $child);
                });
                break;
            default:
                $data = $this->getChild($tags->first(), $type);
                $count = 1;
                break;
        }

        // Get an instance of service
        $service = new FetchService();

        $result = $service->extractRelated($data);

        if ($count == 1) {
            PHPUnit::assertInstanceOf(Related::class, $result);
        } else {
            PHPUnit::assertInstanceOf(Collection::class, $result);
            PHPUnit::assertEquals($count, $result->count());
            foreach ($result as $item) {
                PHPUnit::assertInstanceOf(Related::class, $item);
                PHPUnit::assertContains($item->model->getKey(), $tags->pluck('TAGS_ID')->toArray());
                PHPUnit::assertIsArray($item->metaAttributes);
                PHPUnit::assertEmpty($item->metaAttributes);
            }
        }
    }

    private function getChild($item, $type)
    {
        switch ($type) {
            case 'ri':
                return [
                    Members::TYPE => 'tags',
                    Members::ID => strval($item->getKey()),
                ];
                break;
            case 'model':
                return $item;
                break;
            case 'related':
                return new Related($item);
                break;
        }
    }

    public function extractRelatedProvider()
    {
        return [
            'resource identifier' => [
                'ri',
                null
            ],
            'array of resource identifier' => [
                'array',
                'ri'
            ],
            'array of Model' => [
                'array',
                'model'
            ],
            'array of Related' => [
                'array',
                'related'
            ],
            'Related' => [
                'related',
                null
            ],
            'Model' => [
                'model',
                null
            ],
            'Collection of resource identifier' => [
                'collection',
                'ri'
            ],
            'Collection of Model' => [
                'collection',
                'model'
            ],
            'Collection of Related' => [
                'collection',
                'related'
            ],
        ];
    }

    /**
     * @test
     */
    public function extractRelatedThrowsException()
    {
        // Creates an object with filled out fields
        factory(Photo::class)->create();

        // Creates an instance of request
        $type = 'tags';
        $data = [
            Members::TYPE => $type,
            Members::ID => '666',
        ];

        // Get an instance of service
        $service = new FetchService();

        $this->setFailure(
            JsonApi404Exception::class,
            sprintf(Messages::UPDATING_REQUEST_RELATED_NOT_FOUND, $type)
        );

        $result = $service->extractRelated($data);
    }

    /**
     * @test
     */
    public function getRelatedCollectionFromRequestData()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        $count = 5;
        $tags = factory(Tags::class, $count)->create();
        $model->tags()->attach(
            $tags->pluck('TAGS_ID')->toArray()
        );

        // Creates an instance of request
        $data = $tags->map(function ($item) {
            return [
                Members::TYPE => 'tags',
                Members::ID => strval($item->getKey()),
            ];
        })->toArray();

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelatedFromRequestData($data);

        PHPUnit::assertInstanceOf(Collection::class, $result);
        PHPUnit::assertEquals($count, $result->count());
        foreach ($result as $item) {
            PHPUnit::assertInstanceOf(Related::class, $item);
            PHPUnit::assertContains($item->model->getKey(), $tags->pluck('TAGS_ID')->toArray());
            PHPUnit::assertIsArray($item->metaAttributes);
            PHPUnit::assertEmpty($item->metaAttributes);
        }
    }

    /**
     * @test
     */
    public function getSingleRelatedFromRequestData()
    {
        // Creates an object with filled out fields
        $model = factory(Photo::class)->create();

        $tag = factory(Tags::class)->create();
        $model->tags()->attach(
            $tag->pluck('TAGS_ID')->toArray()
        );

        // Creates an instance of request
        $meta = [
            'key' => 'value'
        ];
        $data = [
            Members::TYPE => 'tags',
            Members::ID => strval($tag->getKey()),
            Members::META => $meta
        ];

        // Get an instance of service
        $service = new FetchService();

        $result = $service->getRelatedFromRequestData($data);

        PHPUnit::assertInstanceOf(Related::class, $result);
        PHPUnit::assertEquals($meta, $result->metaAttributes);
    }
}
