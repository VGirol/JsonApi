<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

class ResourceObject extends BaseResource
{
    use IsData;
    use IsIncluded;
    use HasIdentification;
    use HasRelationships;

    public const DELETED_MESSAGE = 'Object (#%s) successfully deleted.';

    /**
     * Undocumented variable
     *
     * @var \Illuminate\Support\Collection
     */
    protected $includes;

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
        if (!is_null($includes)) {
            $this->includes = $includes;
            return;
        }

        $service = jsonapiInclude();
        $this->includes = $service->parameters();
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @return array
     */
    public function toArray($request): ?array
    {
        if (is_null($this->resource)) {
            if ($this->isResolving) {
                return [static::$wrap => null];
            } else {
                return null;
            }
        }

        // Set identification (i.e., resource type and id)
        $this->setIdentification($request);

        // Add attributes
        $this->setAttributes($request);

        // Add relationships
        $this->setRelationships($request);

        // Add links
        $this->setLinks($request);

        // Add meta
        $this->setMeta($request);

        return $this->resultingArray;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function with($request)
    {
        if (!empty($this->with)) {
            return $this->with;
        }

        // Add document meta
        $this->setDocumentMeta($request);

        if ($this->isUpdate($request) && !$this->haveChanged()) {
            return $this->with;
        }

        // Top-level links member
        if (!$this->isCreation($request)) {
            $this->addDocumentLink(Members::LINK_SELF, $this->getDocumentSelfLink($request));
        }

        // Add document included member
        $this->setIncluded($request);

        return $this->with;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return static|array|null
     */
    public function getContent($request)
    {
        if ($this->isUpdate($request) && !$this->haveChanged()) {
            return $this->hasDocumentMeta($request) ? $this->getDocumentMeta($request) : null;
        }

        return $this;
    }

    /**
     * Check if the resource have had automatic changes.
     *
     * @return bool
     */
    public function haveChanged(): bool
    {
        return ($this->resource != null
            && $this->resource->automaticChanges);
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setAttributes($request)
    {
        $this->setResourceAttributes($this->resource->attributesToArray());
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
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setLinks($request)
    {
        $this->addResourceLink(Members::LINK_SELF, $this->getResourceSelfLink(get_class($this->resource)));
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setDocumentMeta($request)
    {
        // $this->addDocumentMeta('key', 'value');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    protected function isCreation($request): bool
    {
        return ($request->method() == 'POST');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    protected function isUpdate($request): bool
    {
        return in_array($request->method(), ['PATCH', 'PUT']);
    }
}
