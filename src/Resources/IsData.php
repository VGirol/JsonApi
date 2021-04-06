<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;

trait IsData
{
    protected $isResolving = false;

    /**
     * Resolve the resource to an array.
     *
     * @param Request|null $request
     *
     * @return array
     */
    public function resolve($request = null)
    {
        $this->isResolving = true;
        $ret = parent::resolve($request);
        $this->isResolving = false;

        return $ret;
    }
}
