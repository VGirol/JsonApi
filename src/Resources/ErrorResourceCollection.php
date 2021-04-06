<?php

namespace VGirol\JsonApi\Resources;

use VGirol\JsonApiConstant\Members;

class ErrorResourceCollection extends BaseResourceCollection
{
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->wrap(Members::ERRORS);
    }

    public function getStatusCode()
    {
        $max = $this->collection->map(function ($item, $key) {
            return $item->getStatusCode();
        })->max();

        $statusCode = $max;
        if ($this->count() > 1) {
            if ($max >= 400 && $max < 500) {
                $statusCode = 400;
            } elseif ($max >= 500) {
                $statusCode = 500;
            }
        }

        return $statusCode;
    }
}
