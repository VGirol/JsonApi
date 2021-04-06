<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

trait HasIdentification
{
    public function getId()
    {
        return is_null($this->resource) ? null : $this->resource->getKey();
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setIdentification($request)
    {
        $this->setResourceIdentification([
            Members::TYPE => $this->aliases->getResourceType(get_class($this->resource)),
            Members::ID => (string) $this->getId()
        ]);
    }
}
