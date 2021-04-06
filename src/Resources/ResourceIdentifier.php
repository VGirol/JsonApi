<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

class ResourceIdentifier extends BaseResource
{
    use IsData;
    use IsRelationship;
    use HasIdentification;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->wrap(Members::DATA);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): ?array
    {
        if (is_null($this->resource)) {
            if ($this->isResolving) {
                return $this->resultingArray = [static::$wrap => null];
            } else {
                return $this->resultingArray = null;
            }
        }

        // Set identification (i.e., resource type and id)
        $this->setIdentification($request);

        // Add meta
        $this->setMeta($request);

        return $this->resultingArray;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  Request  $request
     * @return array
     */
    public function with($request)
    {
        // Top-level links member
        if ($request->method() != 'POST') {
            $this->addDocumentLink(Members::LINK_SELF, $this->getDocumentSelfLink($request));
            $this->addDocumentLink(Members::LINK_RELATED, $this->getDocumentRelatedLink($request));
        }

        return $this->with;
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setMeta($request)
    {
        // $this->addResourceMeta('key', 'value');

        if (isset($this->resource->pivot)) {
            $this->addResourceMeta('pivot', $this->resource->pivot->attributesToArray());
        }
    }
}
