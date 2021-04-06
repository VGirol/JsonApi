<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiFaker\Laravel\Factory\RelationshipFactory as BaseFactory;

class RelationshipFactory extends BaseFactory
{
    use HasRouteName;
    use HasName;

    // /**
    //  * Undocumented variable
    //  *
    //  * @var string
    //  */
    // protected $name;

    // public function __construct($name, $routeName)
    // {
    //     $this->name = $name;
    //     $this->routeName = $routeName;
    // }

    public function addSelfLink($id)
    {
        $this->addLink(
            Members::LINK_SELF,
            route("{$this->routeName}.relationship.index", ['parentId' => $id, 'relationship' => $this->name])
        );

        return $this;
    }

    public function addRelatedLink($id)
    {
        $this->addLink(
            Members::LINK_RELATED,
            route("{$this->routeName}.related.index", ['parentId' => $id, 'relationship' => $this->name])
        );

        return $this;
    }
}
