<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
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
    }
}
