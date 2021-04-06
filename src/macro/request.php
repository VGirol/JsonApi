<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use VGirol\JsonApi\Tools\DotArray;

Request::macro(
    'getIncludes',
    function (): Collection {
        $includeName = config('query-builder.parameters.include');

        // $includes = collect(explode(',', $this->query($includeName, null)))->filter()
        //     ->map([Str::class, 'camel']);

        return DotArray::associativeFromDotKeys(
            collect(explode(',', $this->query($includeName, null)))->filter()
             ->map([Str::class, 'camel'])
        );
    }
);

Request::macro(
    'getFilters',
    function (): Collection {
        $filterName = config('query-builder.parameters.filter');
        $values = collect($this->all($filterName))->filter()->get($filterName);

        return collect(($values === null) ? [] : $values);
    }
);

Request::macro(
    'getSorts',
    function (): Collection {
        $sortName = config('query-builder.parameters.sort');
        $sortQuery = $this->query($sortName, null);

        $col = collect(explode(',', $sortQuery))
            ->filter()
            ->map(function ($item, $key) {
                // Clean sort parameter
                return in_array($item[0], ['+', '-']) ? $item : "+{$item}";
            });


        // For spatie/laravel-query-builder
        if (!is_null($sortQuery)) {
            $this->query->set($sortName, str_replace('+', null, $sortQuery));
        }

        return $col;
    }
);

Request::macro(
    'getFields',
    function (): Collection {
        $fieldName = config('query-builder.parameters.fields');
        $values = collect($this->all($fieldName))->filter()->get($fieldName);

        return collect(($values === null) ? [] : $values)->map(function ($item) {
            return explode(',', $item);
        });
    }
);
