<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Resources\ResourceIdentifier;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Resources\ResourceObjectCollection;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Services\ExportService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use CanCreateRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            [
                'type' => 'dummyType',
                'route' => 'dummyRoute',
                'model' => 'dummyModel'
            ]
        ];
        config()->set('jsonapi-alias.groups', $config);

        Route::jsonApiResource(
            'dummyRoute'
        );
        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();
    }

    /**
     * @test
     * @dataProvider exportProvider
     */
    public function export($fn, $isSingle, $expectedClass, $objExpectedClass)
    {
        if ($isSingle) {
            $this->exportSingle($fn, $expectedClass);
        } else {
            $this->exportCollection($fn, $expectedClass, $objExpectedClass);
        }
    }

    private function exportSingle($fn, $expectedClass)
    {
        // Creates service
        $service = new ExportService(new AliasesService());

        // Creates an object with filled out fields
        $model = $this->createModel();

        $result = $service->{$fn}($model);

        $this->checkSingle($result, $model, $expectedClass);
    }

    private function exportCollection($fn, $expectedClass, $objExpectedClass)
    {
        // Creates service
        $service = new ExportService(new AliasesService());

        // Creates a fake collection
        $array = $this->createCollection();
        $collection = Collection::make($array);

        // Creates a fake request
        $request = $this->createRequest('dummyRoute', 'GET');

        $result = $service->{$fn}($collection, $request);

        $this->checkCollection($result, $array, $expectedClass, $objExpectedClass);
    }

    private function createModel()
    {
        return $this->getMockForAbstractClass(Model::class, [], 'dummyModel');
    }

    private function createCollection()
    {
        $array = [];
        $count = rand(3, 5);
        for ($i = 0; $i < $count; $i++) {
            $array[] = $this->createModel();
        }

        return $array;
    }

    private function checkCollection($result, $array, $expectedClass, $objExpectedClass)
    {
        PHPUnit::assertInstanceOf($expectedClass, $result);
        PHPUnit::assertInstanceOf(SupportCollection::class, $result->resource);
        PHPUnit::assertEquals(count($array), $result->resource->count());

        foreach ($result->resource as $key => $res) {
            $this->checkSingle($res, $array[$key], $objExpectedClass);
        }
    }

    private function checkSingle($result, $model, $expectedClass)
    {
        PHPUnit::assertInstanceOf($expectedClass, $result);
        PHPUnit::assertSame($model, $result->resource);
    }

    public function exportProvider()
    {
        return [
            'Single ResourceObject' => [
                'exportSingleResource',
                true,
                ResourceObject::class,
                null
            ],
            'Single ResourceIdentifier' => [
                'exportSingleResourceIdentifier',
                true,
                ResourceIdentifier::class,
                null
            ],
            'Collection of ResourceObject' => [
                'exportCollectionOfResource',
                false,
                ResourceObjectCollection::class,
                ResourceObject::class
            ],
            'Collection of ResourceIdentifierObject' => [
                'exportCollectionOfResourceIdentifier',
                false,
                ResourceIdentifierCollection::class,
                ResourceIdentifier::class
            ],
            'Export as resource : single' => [
                'exportAsResource',
                true,
                ResourceObject::class,
                null
            ],
            'Export as resource : collection' => [
                'exportAsResource',
                false,
                ResourceObjectCollection::class,
                ResourceObject::class
            ],
            'Export as resource identifier : single' => [
                'exportAsResourceIdentifier',
                true,
                ResourceIdentifier::class,
                null
            ],
            'Export as resource identifier : collection' => [
                'exportAsResourceIdentifier',
                false,
                ResourceIdentifierCollection::class,
                ResourceIdentifier::class
            ],
        ];
    }
}
