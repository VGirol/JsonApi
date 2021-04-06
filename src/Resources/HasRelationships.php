<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasRelationships
{
    /**
     * Undocumented function
     * Could be overloaded, but it is not recommended.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function setRelationships($request)
    {
        foreach ($this->includes->keys() as $relName) {
            // Get relationship
            $relationship = $this->getRelationship($request, $relName);

            // Add relationship
            $this->addResourceRelationship($relName, $relationship);
        }
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     * @param string $relationshipName
     *
     * @return array
     */
    private function getRelationship($request, string $relationshipName)
    {
        // Get relationship collection
        $elm = $this->resource->getRelation($relationshipName);

        if ($elm instanceof Collection) {
            $className = $this->aliases->getResourceIdentifierCollectionClassName($relationshipName);
        } else {
            $className = $this->aliases->getResourceIdentifierClassName($relationshipName);
        }

        return call_user_func_array(
            [$className, 'make'],
            [&$elm, $this->aliases]
        )->asRelationship($request, $this->resource, $relationshipName);
    }

    /**
     * Undocumented function
     * Could be overloaded, but it is not recommended.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return static
     */
    protected function setIncluded($request)
    {
        /** @var Collection $included A collection of ResourceObject objects */
        $included = $this->getAllIncludedResources();

        if ($included->isEmpty()) {
            return;
        }

        $this->addIncludedResources(
            $included->map->asIncluded($request)->toArray()
        );

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return Collection
     */
    public function getAllIncludedResources()
    {
        $collection = collect([]);
        foreach ($this->includes as $relName => $include) {
            /** @var array $include */

            // Retrieve included resources
            /** @var Collection $inc */
            $inc = $this->retrieveIncludedTree($relName, collect($include));
            if ($inc->isEmpty()) {
                continue;
            }

            $collection = $collection->merge($inc);
        }

        return $collection->unique()->values();
    }

    /**
     * Returns a collection of ResourceObject objects.
     *
     * @param string     $relationshipName
     * @param Collection $include
     *
     * @return Collection
     */
    private function retrieveIncludedTree($relationshipName, $include)
    {
        // Get related collection
        if (!$this->resource->relationLoaded($relationshipName)) {
            $this->resource->load($relationshipName);
        }
        $elm = $this->resource->getRelation($relationshipName);
        if (!($elm instanceof Collection)) {
            $elm = collect([$elm])->filter();
        }

        // Get resource class name
        $className = $this->aliases->getResourceClassName($relationshipName);

        $inc = $elm->map(function ($item) use ($className, $include) {
            return call_user_func_array(
                [$className, 'make'],
                [$item, $include]
            );
        });

        if ($inc->isNotEmpty() && $include->isNotEmpty()) {
            $inc = $inc->merge(
                $inc->flatmap(
                    function ($item) use ($include) {
                       return $item->getAllIncludedResources();
                    }
                )
            );
        }

        return $inc->unique();
    }
}
