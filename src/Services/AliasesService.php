<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Resources\ResourceIdentifier;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Resources\ResourceObjectCollection;

class AliasesService
{
    public const ERROR_PATH_DOES_NOT_EXIST =
    'Path does not exist (%s => %s).';

    public const ERROR_REF_NOT_VALID =
    'Reference "%s" is not valid : no such Model, resource type, route key or relationship alias.';

    public const ERROR_NO_ROUTE =
    'No route available !';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $groups = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $models = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $routeKeys = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $resType = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $aliases = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function init(): void
    {
        $this->groups = config('jsonapi-alias.groups', []);
        $this->createDictionaries();
    }

    public function getModelClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'model', true);
    }

    public function getParentClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'model', false);
    }

    public function getResourceClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'resource-ro', true);
    }

    public function getResourceIdentifierClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'resource-ri', true);
    }

    public function getResourceCollectionClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'resource-roc', true);
    }

    public function getResourceIdentifierCollectionClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'resource-ric', true);
    }

    public function getFormRequestClassName($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'request', true);
    }

    public function getResourceType($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'type', true);
    }

    public function getResourceRoute($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'route', true);
    }

    public function getParentRoute($ref = null): string
    {
        return $this->getDictionaryValue($ref, 'route', false);
    }

    public function getModelKeyName($ref = null): string
    {
        return jsonapiModel()->getModelKeyName($this->getModelClassName($ref));
    }

    /**
     * Undocumented function
     *
     * @param string|null $ref
     *
     * @return int|null
     */
    protected function getIndex(?string $ref): ?int
    {
        if (isset($this->aliases[$ref])) {
            $ref = $this->aliases[$ref];
        }

        if (isset($this->routeKeys[$ref])) {
            return $this->routeKeys[$ref];
        }
        if (isset($this->models[$ref])) {
            return $this->models[$ref];
        }
        if (isset($this->resType[$ref])) {
            return $this->resType[$ref];
        }

        return null;
    }

    /**
     * Undocumented function
     *
     * @param Request|Route|Model|string|null $ref
     * @param string                          $query
     * @param bool                            $searchForRelated
     *
     * @return string
     * @throws JsonApiException
     */
    private function getDictionaryValue($ref, string $query, bool $searchForRelated): string
    {
        if (!\is_string($ref)) {
            $ref = $this->getKeyName($ref, $searchForRelated);
        }
        $index = $this->getIndex($ref);
        if ($index === null) {
            throw new JsonApiException(sprintf(self::ERROR_REF_NOT_VALID, $ref));
        }

        $value = $this->groups[$index];
        $keys = explode('.', $query);
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
                break;
            }

            switch ($key) {
                case 'resource-ro':
                    $value = ResourceObject::class;
                    break;
                case 'resource-ri':
                    $value = ResourceIdentifier::class;
                    break;
                case 'resource-roc':
                    $value = ResourceObjectCollection::class;
                    break;
                case 'resource-ric':
                    $value = ResourceIdentifierCollection::class;
                    break;
                default:
                    throw new JsonApiException(sprintf(self::ERROR_PATH_DOES_NOT_EXIST, $ref, $query));
            }
        }

        return $value;
    }

    /**
     * Undocumented function
     *
     * @param bool                            $searchForRelated
     * @param Request|Route|Model|string|null $ref
     *
     * @return string
     * @throws JsonApiException
     */
    private function getKeyName($ref, $searchForRelated): string
    {
        if ($ref instanceof Model) {
            $ref = get_class($ref);
        }

        if (\is_string($ref)) {
            return $ref;
        }

        $route = $ref;
        if (\is_null($ref)) {
            $ref = request();
        }
        if ($ref instanceof Request) {
            $route = $ref->route();
        }
        if (!$route instanceof Route) {
            throw new JsonApiException(self::ERROR_NO_ROUTE);
        }

        $bRelated = $route->named(['*.related.*', '*.relationship.*']) && $searchForRelated;
        $segment = $bRelated ? $route->parameters['relationship'] : $this->getRootSegment($route);

        return $segment;
    }

    /**
     * Undocumented function
     *
     * @param Route $route
     *
     * @return string
     */
    private function getRootSegment($route): string
    {
        $path = array_values(array_diff(
            explode('/', $route->uri),
            explode('/', $route->getPrefix())
        ));

        return $path[0];
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function createDictionaries(): void
    {
        $this->routeKeys = [];
        $this->models = [];
        $this->resType = [];
        $this->aliases = [];

        foreach ($this->groups as $key => $array) {
            if (isset($array['route'])) {
                $this->routeKeys[$array['route']] = $key;
            }
            if (isset($array['model'])) {
                $this->models[$array['model']] = $key;
            }
            if (isset($array['type'])) {
                $this->resType[$array['type']] = $key;
            }
            if (isset($array['relationships'])) {
                $this->aliases = array_merge($this->aliases, $array['relationships']);
            }
        }
    }
}
