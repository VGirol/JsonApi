<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Support\Collection;
use VGirol\JsonApiConstant\Members;

class PaginationService extends AbstractService
{
    /**
     * Undocumented variable
     *
     * @var integer
     */
    protected $totalItem;

    /**
     * Undocumented variable
     *
     * @var Collection
     */
    protected $options;

    protected function getConfigKey(): string
    {
        return 'pagination';
    }

    protected function parseParameters($request)
    {
        return Collection::make($request->query(config('json-api-paginate.pagination_parameter'), null));
    }

    public function queryIsValid(): bool
    {
        return true;
    }

    public function parseRequest($request = null, $force = false)
    {
        parent::parseRequest($request, $force);

        return $this->fillOptions();
    }

    /**
     * Undocumented function
     *
     * @return static
     */
    protected function fillOptions()
    {
        $number_parameter = config('json-api-paginate.number_parameter');
        $size_parameter = config('json-api-paginate.size_parameter');

        $page = $this->value($number_parameter) ?: 1;
        $itemPerPage = $this->value($size_parameter) ?: config('json-api-paginate.max_results');
        $pageCount = intdiv($this->totalItem, $itemPerPage);
        if ($this->totalItem % $itemPerPage != 0) {
            $pageCount++;
        }
        if ($pageCount == 0) {
            $pageCount = 1;
        }

        $this->options = Collection::make([
            'number_parameter' => $number_parameter,
            'size_parameter' => $size_parameter,
            'total_items' => $this->totalItem,
            'item_per_page' => $itemPerPage,
            'page_count' => $pageCount,
            'page' => $page
        ]);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param integer $total
     *
     * @return void
     */
    public function setTotalItem(int $total)
    {
        $this->totalItem = $total;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPaginationMeta(): array
    {
        return $this->options->only(['total_items', 'item_per_page', 'page_count', 'page'])->toArray();
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getPaginationLinks(): array
    {
        $route = request()->route();
        $selfRouteName = $route->getName();
        $page_parameter = config('json-api-paginate.pagination_parameter');

        // Set document "links" member of json response
        $links = [
            Members::LINK_PAGINATION_FIRST => [
                'route' => $selfRouteName,
                'query' => [
                    "{$page_parameter}[{$this->options['number_parameter']}]" => 1,
                    "{$page_parameter}[{$this->options['size_parameter']}]" => $this->options['item_per_page']
                ]
            ],
            Members::LINK_PAGINATION_LAST => [
                'route' => $selfRouteName,
                'query' => [
                    "{$page_parameter}[{$this->options['number_parameter']}]" => $this->options['page_count'],
                    "{$page_parameter}[{$this->options['size_parameter']}]" => $this->options['item_per_page']
                ]
            ],
            Members::LINK_PAGINATION_PREV => null,
            Members::LINK_PAGINATION_NEXT => null
        ];
        if ($this->options['total_items'] > $this->options['item_per_page']) {
            if ($this->options['page'] > 1) {
                $links[Members::LINK_PAGINATION_PREV] = [
                    'route' => $selfRouteName,
                    'query' => [
                        "{$page_parameter}[{$this->options['number_parameter']}]" => $this->options['page'] - 1,
                        "{$page_parameter}[{$this->options['size_parameter']}]" => $this->options['item_per_page']
                    ]
                ];
            }
            if ($this->options['page'] < $this->options['page_count']) {
                $links[Members::LINK_PAGINATION_NEXT] = [
                    'route' => $selfRouteName,
                    'query' => [
                        "{$page_parameter}[{$this->options['number_parameter']}]" => $this->options['page'] + 1,
                        "{$page_parameter}[{$this->options['size_parameter']}]" => $this->options['item_per_page']
                    ]
                ];
            }
        }

        foreach ($links as $name => $params) {
            if (is_null($params)) {
                $url = null;
            } else {
                $url = route(
                    $params['route'],
                    array_merge(
                        jsonapiFields()->getQueryParameter(),
                        jsonapiFilter()->getQueryParameter(),
                        jsonapiInclude()->getQueryParameter(),
                        jsonapiSort()->getQueryParameter(),
                        $params['query'],
                        $route->parameters
                    )
                );
            }
            $links[$name] = $url;
        }

        return $links;
    }
}
