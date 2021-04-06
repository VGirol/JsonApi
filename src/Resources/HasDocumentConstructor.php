<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Support\Arr;
use VGirol\JsonApiConstant\Members;

trait HasDocumentConstructor
{
    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $resultingArray;

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addDocumentMeta(string $key, $value)
    {
        return $this->addToWith(Members::META, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getDocumentMeta($request)
    {
        return $this->getFromWith(Members::META, $request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function hasDocumentMeta($request): bool
    {
        return $this->has($this->with($request), Members::META);
    }

    /**
     * Undocumented function
     *
     * @param string            $key
     * @param array|string|null $value
     *
     * @return static
     */
    public function addDocumentLink(string $key, $value)
    {
        return $this->addToWith(Members::LINKS, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getDocumentLinks($request)
    {
        return $this->getFromWith(Members::LINKS, $request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function hasDocumentLinks($request): bool
    {
        return $this->has($this->with($request), Members::LINKS);
    }

    /**
     * Undocumented function
     *
     * @param array $identification
     *
     * @return void
     */
    public function setResourceIdentification(array $identification)
    {
        if (!isset($this->resultingArray)) {
            $this->resultingArray = [];
        }

        return $this->resultingArray = array_merge($this->resultingArray, $identification);
    }

    /**
     * Undocumented function
     *
     * @param array $attributes
     *
     * @return void
     */
    public function setResourceAttributes(array $attributes)
    {
        return $this->set('resultingArray', Members::ATTRIBUTES, $attributes);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addResourceAttribute(string $key, $value)
    {
        return $this->addToResultingArray(Members::ATTRIBUTES, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addResourceRelationship(string $key, $value)
    {
        return $this->addToResultingArray(Members::RELATIONSHIPS, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addResourceMeta(string $key, $value)
    {
        return $this->addToResultingArray(Members::META, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string            $key
     * @param array|string|null $value
     *
     * @return static
     */
    public function addResourceLink(string $key, $value)
    {
        return $this->addToResultingArray(Members::LINKS, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addRelationshipMeta(string $key, $value)
    {
        return $this->addToAdditional(Members::META, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string            $key
     * @param array|string|null $value
     *
     * @return static
     */
    public function addRelationshipLink(string $key, $value)
    {
        return $this->addToAdditional(Members::LINKS, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param array $resource A single resource object or an array of resource objects
     *
     * @return static
     */
    public function addIncludedResources($resource)
    {
        return $this->addToWith(Members::INCLUDED, $resource);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getIncluded($request)
    {
        return $this->getFromWith(Members::INCLUDED, $request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function hasIncluded($request): bool
    {
        return $this->has($this->with($request), Members::INCLUDED);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addErrorMeta(string $key, $value)
    {
        return $this->addToResultingArray(Members::META, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function addJsonapiMeta(string $key, $value)
    {
        if (!isset($this->with[Members::JSONAPI])) {
            $this->with[Members::JSONAPI] = [];
        }
        if (!isset($this->with[Members::JSONAPI][Members::META])) {
            $this->with[Members::JSONAPI][Members::META] = [];
        }
        $this->with[Members::JSONAPI][Members::META][$key] = $value;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param mixed $value
     *
     * @return static
     */
    public function addJsonapiVersion($value)
    {
        return $this->addToWith(Members::JSONAPI, $value, Members::JSONAPI_VERSION);
    }

    /**
     * Undocumented function
     *
     * @param string $member
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    private function addToResultingArray(string $member, $value, string $key = null)
    {
        return $this->addTo('resultingArray', $member, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $member
     * @param mixed  $value
     * @param string $key
     *
     * @return static
     */
    private function addToWith(string $member, $value, string $key = null)
    {
        return $this->addTo('with', $member, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $member
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    private function addToAdditional(string $member, $value, string $key = null)
    {
        return $this->addTo('additional', $member, $value, $key);
    }

    /**
     * Undocumented function
     *
     * @param string $property
     * @param string $member
     * @param mixed  $value
     * @param string $key
     *
     * @return static
     */
    private function addTo(string $property, string $member, $value, string $key = null)
    {
        if (!isset(($this->$property)[$member])) {
            ($this->$property)[$member] = [];
        }
        if (is_null($key)) {
            if (Arr::isAssoc($value)) {
                $value = [$value];
            }
            ($this->$property)[$member] = array_merge(($this->$property)[$member], $value);
        } else {
            ($this->$property)[$member][$key] = $value;
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $property
     * @param string $member
     * @param mixed  $value
     *
     * @return static
     */
    private function set(string $property, string $member, $value)
    {
        if (!isset(($this->$property)[$member])) {
            ($this->$property)[$member] = [];
        }
        ($this->$property)[$member] = $value;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array  $member
     * @param string $key
     *
     * @return boolean
     */
    private function has(array $member, string $key): bool
    {
        return array_key_exists($key, $member);
    }

    /**
     * Undocumented function
     *
     * @param string  $key
     * @param Request $request
     *
     * @return mixed
     */
    private function getFromWith(string $key, $request)
    {
        return $this->getFrom($this->with($request), $key);
    }

    /**
     * Undocumented function
     *
     * @param string  $key
     * @param Request $request
     *
     * @return mixed
     */
    private function getFromAdditional(string $key)
    {
        return $this->getFrom($this->additional, $key);
    }

    /**
     * Undocumented function
     *
     * @param array  $member
     * @param string $key
     *
     * @return mixed
     */
    private function getFrom(array $member, string $key)
    {
        return array_filter(
            $member,
            function ($k) use ($key) {
                return $k == $key;
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
