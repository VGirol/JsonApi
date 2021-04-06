<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

class ResourceIdentifierCollection extends BaseResourceCollection
{
    use IsData;
    use IsRelationship;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->wrap(Members::DATA);
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  Request  $request
     * @return array
     */
    public function with($request)
    {
        $this->addDocumentLink(Members::LINK_SELF, $this->getDocumentSelfLink($request));
        $this->addDocumentLink(Members::LINK_RELATED, $this->getDocumentRelatedLink($request));

        // if (jsonapiPagination()->allowed($request)) {
        //     if ($this->isRelationship()) {
        //         if ($this->hasToManyResults()) {
        //             $this->addToMeta('total_items', $this->resource->count());
        //         }
        //     } else {
        //         $this->addToMeta('pagination', jsonapiPagination()->getPaginationMeta());

        //         foreach (jsonapiPagination()->getPaginationLinks() as $name => $url) {
        //             $this->addToLinks($name, $url);
        //         }
        //     }
        // }

        return $this->with;
    }
}
