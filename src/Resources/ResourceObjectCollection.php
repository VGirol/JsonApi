<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

class ResourceObjectCollection extends BaseResourceCollection
{
    use IsData;

    public function __construct($resource, $includes = null)
    {
        parent::__construct($resource);

        $this->wrap(Members::DATA);
        $this->initIncludes($includes);
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Support\Collection $includes
     *
     * @return void
     */
    public function initIncludes($includes)
    {
        if (is_null($includes)) {
            $service = jsonapiInclude();
            $includes = $service->parameters();
        }

        $this->collection->each(function ($item) use ($includes) {
            $item->initIncludes($includes);
        });
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  Request  $request
     *
     * @return array
     */
    public function with($request)
    {
        // Add document (top-level) "self" link
        $this->addDocumentLink(Members::LINK_SELF, $this->getDocumentSelfLink($request));

        // Add pagination meta and links
        if (jsonapiPagination()->allowed($request)) {
            $this->addDocumentMeta(Members::META_PAGINATION, jsonapiPagination()->getPaginationMeta());

            foreach (jsonapiPagination()->getPaginationLinks() as $name => $url) {
                $this->addDocumentLink($name, $url);
            }
        }

        // Add top-level included member
        $this->setIncluded($request);

        return $this->with;
    }

    /**
     * Transform the resource collection into an array used as included resource.
     *
     * @param Request $request
     * @param Collection $includes
     *
     * @return array
     */
    public function asIncluded($request): array
    {
        $data = $this->collection->map->asIncluded($request)->all();

        return array_merge_recursive($data, $this->additional);
    }

    /**
     * Undocumented function
     * Could be overloaded, but it is not recommended.
     *
     * @param Request $request
     */
    protected function setIncluded($request)
    {
        /** @var Collection $included A collection of ResourceObject objects */
        $included = $this->collection->flatMap(function ($item) {
            return $item->getAllIncludedResources();
        })->unique(function ($item) {
            return $this->aliases->getResourceType(\get_class($item->resource)) . '_' . $item->getId();
        })->values()->filter();

        if ($included->isEmpty()) {
            return;
        }

        $this->addIncludedResources(
            $included->map->asIncluded($request)->toArray()
        );
    }
}
