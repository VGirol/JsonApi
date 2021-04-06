<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Services\PaginationService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApi\Tests\UsesTools;

class PaginationServiceTest extends TestCase
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
    }

    private function getRequest($nb, $size, $url): Request
    {
        $configKey = config('json-api-paginate.pagination_parameter');
        $value = [
            config('json-api-paginate.number_parameter') => $nb,
            config('json-api-paginate.size_parameter') => $size
        ];
        $request = $this->createRequest($url, 'GET', [$configKey => $value]);

        return $request;
    }

    private function getService($nb, $size, $url = '/photos'): PaginationService
    {
        $this->swap('request', $this->getRequest($nb, $size, $url));

        return resolve(PaginationService::class);
    }

    /**
     * @test
     */
    public function parseRequest()
    {
        $nb = 2;
        $size = 20;
        $service = $this->getService($nb, $size);

        PHPUnit::assertNull($service->parameters());

        $obj = $service->parseRequest();

        PHPUnit::assertSame($obj, $service);

        $parameters = $service->parameters();

        $expected = [
            config('json-api-paginate.number_parameter') => $nb,
            config('json-api-paginate.size_parameter') => $size
        ];

        PHPUnit::assertInstanceOf(Collection::class, $parameters);
        PHPUnit::assertEquals($expected, $parameters->toArray());
    }

    /**
     * @test
     */
    public function parseRequestTwiceWithDefaultParametersUseCache()
    {
        $nb = 2;
        $size = 20;
        $service = $this->getService($nb, $size);

        PHPUnit::assertNull($service->parameters());

        $service->parseRequest();

        $first = $service->parameters();

        $expected = [
            config('json-api-paginate.number_parameter') => $nb,
            config('json-api-paginate.size_parameter') => $size
        ];

        PHPUnit::assertInstanceOf(Collection::class, $first);
        PHPUnit::assertEquals($expected, $first->toArray());

        $request = $this->createRequest('/', 'GET', ['page' => ['number' => $nb, 'size' => $size]]);
        $service->parseRequest($request);

        PHPUnit::assertSame($first, $service->parameters());
    }

    /**
     * @test
     * @dataProvider getPaginationMetaProvider
     */
    public function getPaginationMeta($nb, $size, $total, $expected)
    {
        $service = $this->getService($nb, $size);
        $service->setTotalItem($total);
        $service->parseRequest();

        PHPUnit::assertEquals($expected, $service->getPaginationMeta());
    }

    public function getPaginationMetaProvider()
    {
        return [
            [
                1,
                20,
                0,
                [
                    'total_items' => 0,
                    'item_per_page' => 20,
                    'page_count' => 1,
                    'page' => 1
                ]
            ],
            [
                1,
                20,
                5,
                [
                    'total_items' => 5,
                    'item_per_page' => 20,
                    'page_count' => 1,
                    'page' => 1
                ]
            ],
            [
                2,
                20,
                40,
                [
                    'total_items' => 40,
                    'item_per_page' => 20,
                    'page_count' => 2,
                    'page' => 2
                ]
            ],
            [
                2,
                20,
                50,
                [
                    'total_items' => 50,
                    'item_per_page' => 20,
                    'page_count' => 3,
                    'page' => 2
                ]
            ],
            [
                null,
                20,
                50,
                [
                    'total_items' => 50,
                    'item_per_page' => 20,
                    'page_count' => 3,
                    'page' => 1
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getPaginationLinksProvider
     */
    public function getPaginationLinks($nb, $size, $total, $expected)
    {
        $service = $this->getService($nb, $size);
        $service->setTotalItem($total);
        $service->parseRequest();

        array_walk(
            $expected,
            function (&$value, $key) {
                if ($value !== null) {
                    $value = route('photos.index', $value);
                }
            }
        );

        PHPUnit::assertEquals($expected, $service->getPaginationLinks());
    }

    public function getPaginationLinksProvider()
    {
        return [
            [
                1,
                20,
                0,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 1, 'page[size]' => 20],
                    'prev' => null,
                    'next' => null
                ]
            ],
            [
                1,
                20,
                5,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 1, 'page[size]' => 20],
                    'prev' => null,
                    'next' => null
                ]
            ],
            [
                1,
                20,
                20,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 1, 'page[size]' => 20],
                    'prev' => null,
                    'next' => null
                ]
            ],
            [
                1,
                20,
                50,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 3, 'page[size]' => 20],
                    'prev' => null,
                    'next' => ['page[number]' => 2, 'page[size]' => 20]
                ]
            ],
            [
                2,
                20,
                50,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 3, 'page[size]' => 20],
                    'prev' => ['page[number]' => 1, 'page[size]' => 20],
                    'next' => ['page[number]' => 3, 'page[size]' => 20]
                ]
            ],
            [
                3,
                20,
                50,
                [
                    'first' => ['page[number]' => 1, 'page[size]' => 20],
                    'last' => ['page[number]' => 3, 'page[size]' => 20],
                    'prev' => ['page[number]' => 2, 'page[size]' => 20],
                    'next' => null
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function getPaginationLinksWithOtherQueries()
    {
        $url = '/photos';
        $nb = 3;
        $size = 5;
        $total = 20;

        $pageParameter = config('json-api-paginate.pagination_parameter');
        $nbParameter = config('json-api-paginate.number_parameter');
        $sizeParameter = config('json-api-paginate.size_parameter');
        $others = [
            'fields' => ['type1' => 'field1,field2'],
            'filter' => ['type1' => 'field1'],
            'include' => 'resource1',
            'sort' => 'field1, -field2',
        ];
        $queries = array_merge(
            [
                $pageParameter => [
                    $nbParameter => $nb,
                    $sizeParameter => $size
                ]
            ],
            $others
        );
        $request = $this->createRequest($url, 'GET', $queries);
        $this->swap('request', $request);

        $service = resolve(PaginationService::class);
        $service->setTotalItem($total);
        $service->parseRequest();

        // page[number]
        $nbKey = "{$pageParameter}[{$nbParameter}]";
        // page[size]
        $sizeKey = "{$pageParameter}[{$sizeParameter}]";
        $expected = [
            'first' => [$nbKey => 1, $sizeKey => $size],
            'last' => [$nbKey => 4, $sizeKey => $size],
            'prev' => [$nbKey => 2, $sizeKey => $size],
            'next' => [$nbKey => 4, $sizeKey => $size]
        ];
        array_walk(
            $expected,
            function (&$value, $key) use ($others) {
                if ($value !== null) {
                    $value = route('photos.index', array_merge($others, $value));
                }
            }
        );

        PHPUnit::assertEquals($expected, $service->getPaginationLinks());
    }

    /**
     * @test
     */
    public function queryIsValid()
    {
        $service = resolve(PaginationService::class);

        PHPUnit::assertTrue($service->queryIsValid());
    }

    /**
     * @test
     */
    public function getConfigKey()
    {
        $service = $this->getService(2, 20);

        $expected = [
            'pagination' => [
                'number' => 2,
                'size' => 20
            ]
        ];

        PHPUnit::assertEquals($expected, $service->getQueryParameter());
    }
}
