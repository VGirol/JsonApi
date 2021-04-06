<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseResourceCollection extends ResourceCollection
{
    use HasDocumentConstructor;
    use CanCreateLink;

    protected $aliases;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->aliases = jsonapiAliases();
        $this->resource = $this->collectResource($resource);
    }
}
