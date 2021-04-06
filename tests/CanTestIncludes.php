<?php

namespace VGirol\JsonApi\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use VGirol\JsonApi\Tests\Tools\Factory\HelperFactory;
use VGirol\JsonApiConstant\Members;

trait CanTestIncludes
{
    /**
     * Undocumented function
     *
     * @param Model|Collection $collection
     * @param array $relationships
     *
     * @return void
     */
    protected function checkAllIncludes($collection, array $relationships, $callback)
    {
        if (!($collection instanceof Collection)) {
            $collection = collect([$collection]);
        }

        foreach ($relationships as $path) {
            $colTemp = clone $collection;
            $this->checkIncludes($colTemp, $path, $callback);
        }
    }

    /**
     * Undocumented function
     *
     * @param Model|Collection $collection
     * @param string $path
     *
     * @return void
     */
    protected function checkIncludes($collection, string $path, $callback)
    {
        $pathParts = explode('.', $path);
        while (!empty($pathParts) && $collection->isNotEmpty()) {
            $part = array_shift($pathParts);
            $parent = $this->getResourceType(get_class($collection->first()));
            $child = $this->getAlias($parent, $part);

            $collection = $collection->flatMap(function ($item) use ($part) {
                $child = $item->{$part};
                if (!($child instanceof Collection)) {
                    $child = collect([$child]);
                }
                return $child;
            })->unique(function ($item) use ($part) {
                return $part . '_' . $item->getKey();
            })->values();

            // Creates the expected collection
            $factory = (new HelperFactory())->roCollection(
                $collection,
                Str::singular($child),
                $child
            )
                ->each(function ($resource) use ($child, $pathParts) {
                    $routeName = Str::plural($child);

                    // Add self link to each item of the collection
                    $resource->addLinks(
                        [
                            Members::LINK_SELF => route("{$routeName}.show", ['id' => $resource->getKey()])
                        ]
                    );

                    // If required, add relationship to eache item of the collection
                    if (!empty($pathParts)) {
                        $resource->appendRelationships([$pathParts[0]]);
                    }
                });

            $expected = $factory->toArray();

            // Checks included object
            $callback($expected);
        }
    }

    private function getAlias(string $parent, string $name): string
    {
        foreach ($this->getToolsAliases() as $group) {
            if ($group['type'] != $parent) {
                continue;
            }
            if (!isset($group['relationships'])) {
                return $name;
            }
            return $group['relationships'][$name] ?? $name;
        }

        return $name;
    }

    private function getResourceType(string $name): string
    {
        foreach ($this->getToolsAliases() as $group) {
            if ($group['model'] != $name) {
                continue;
            }
            return $group['type'];
        }

        throw new \Exception('No model.');
    }
}
