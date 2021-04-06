<?php

namespace VGirol\JsonApi\Tests;

use VGirol\JsonApiConstant\Members;

class PaginationHelper
{
    public static function getPaginationMeta($options)
    {
        return [
            'total_items' => $options['itemCount'],
            'item_per_page' => $options['itemPerPage'],
            'page_count' => $options['pageCount'],
            'page' => $options['page']
        ];
    }

    public static function createPaginationLinks($routeName, $options, $prev = false, $next = false)
    {
        return [
            Members::LINK_PAGINATION_FIRST => static::linkFactory(
                $routeName,
                1,
                $options['itemPerPage'],
                $options['routeParameters']
            ),
            Members::LINK_PAGINATION_LAST => static::linkFactory(
                $routeName,
                $options['pageCount'],
                $options['itemPerPage'],
                $options['routeParameters']
            ),
            Members::LINK_PAGINATION_PREV => $prev ? static::linkFactory(
                $routeName,
                $options['page'] - 1,
                $options['itemPerPage'],
                $options['routeParameters']
            ) : null,
            Members::LINK_PAGINATION_NEXT => $next ? static::linkFactory(
                $routeName,
                $options['page'] + 1,
                $options['itemPerPage'],
                $options['routeParameters']
            ) : null
        ];
    }

    public static function linkFactory(string $routeName, int $page, ?int $itemPerPage, array $parameters)
    {
        $number_parameter = config('json-api-paginate.number_parameter');
        $size_parameter = config('json-api-paginate.size_parameter');
        $pagination_parameter = config('json-api-paginate.pagination_parameter');

        return route(
            $routeName,
            array_merge(
                $parameters,
                [
                    "{$pagination_parameter}[{$number_parameter}]" => $page,
                    "{$pagination_parameter}[{$size_parameter}]" => $itemPerPage
                ]
            )
        );
    }
}
