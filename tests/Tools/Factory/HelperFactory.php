<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use VGirol\JsonApiFaker\Laravel\Generator;

class HelperFactory extends Generator
{
    public function __construct()
    {
        parent::__construct();

        $this->setFactory('jsonapi', JsonapiFactory::class)
            ->setFactory('relationship', RelationshipFactory::class)
            ->setFactory('resource-object', ResourceObjectFactory::class);
    }

    /**
     * @return ResourceObjectFactory
     */
    public function resourceObject(...$args)
    {
        $resource = $this->create('resource-object', ...$args);

        if (isset($args[2])) {
            $resource->setRouteName($args[2]);
        }

        return $resource;
    }
}
