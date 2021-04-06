<?php

namespace VGirol\JsonApi\Tests\Tools\Factory;

use Illuminate\Support\Facades\Route;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiFaker\Laravel\Factory\ResourceObjectFactory as BaseFactory;

class ResourceObjectFactory extends BaseFactory
{
    use HasRouteName;

    public function addSelfLink()
    {
        return $this->addLink(
            Members::LINK_SELF,
            route(
                $this->routeName . '.show',
                [
                    Members::ID => $this->id
                ]
            )
        );
    }
    // public function __construct($model = null, ?string $resourceType = null, ?string $routeName = null)
    // {
    //     parent::__construct($model, $resourceType);

    //     if ($routeName === null) {
    //         throw new \Exception('Stop !');
    //     }
    //     $this->setRouteName($routeName);
    // }

    public function appendRelationships(array $relationships)
    {
        $relationships = array_fill_keys($relationships, null);
        array_walk(
            $relationships,
            function (&$value, $key) {
                $path = explode('.', $key);
                $value = jsonapiAliases()->getResourceType($path[0]);
            }
        );

        return parent::appendRelationships($relationships);
    }

    public function getLocationUrl(): ?string
    {
        $routeName = $this->routeName . '.show';
        $params = [$this->resourceType => $this->id];

        return Route::has($routeName) ? route($routeName, $params) : null;
    }

    // /**
    //  * Undocumented function
    //  *
    //  * @param mixed ...$args
    //  * @return RelationshipFactory
    //  */
    // protected function createRelationshipFactory(...$args)
    // {
    //     // $arguments = array_merge(['relationship'], $args, [$this->routeName]);
    //     $arguments = array_merge(['relationship'], $args);

    //     return (new HelperFactory())->create(...$arguments);
    //     // return call_user_func_array([HelperFactory::class, 'create'], $arguments);
    // }

    /**
     * Undocumented function
     *
     * @param RelationshipFactory $relationship
     * @param string $name
     * @param string $resourceType
     * @return static
     */
    protected function fillRelationship($relationship, string $name, string $resourceType)
    {
        parent::fillRelationship($relationship, $name, $resourceType);

        $relationship->setName($name)
            ->setRouteName($this->routeName)
            ->addSelfLink($this->model->getKey())
            ->addRelatedLink($this->model->getKey());

        return $this;
    }
}
