<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiFaker\Exception\JsonApiFakerException;
use VGirol\JsonApiFaker\Laravel\Factory\RoCollectionFactory;

class CollectionFactory extends RoCollectionFactory
{
    protected function transform($collection, $resourceType): array
    {
        $array = parent::transform($collection, $resourceType);

        $result = array_walk(
            $array,
            /**
             * @param ResourceObjectFactory $item
             *
             * @return void
             */
            function ($item) {
                $item->addLink(Members::LINK_SELF, $item->getLocationUrl());
            }
        );

        if ($result === false) {
            throw new JsonApiFakerException('Error');
        }

        return $array;
    }
}
