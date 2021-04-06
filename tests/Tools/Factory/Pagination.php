<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use VGirol\JsonApiFaker\Laravel\Helpers\Pagination as BasePagination;

class Pagination extends BasePagination
{
    protected static function getDefaultOptions(): array
    {
        $array = parent::getDefaultOptions();
        $array['itemPerPage'] = config('json-api-paginate.max_results');

        return $array;
    }
}
