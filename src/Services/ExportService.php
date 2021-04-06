<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use VGirol\JsonApi\Resources\ResourceIdentifier;
use VGirol\JsonApi\Resources\ResourceIdentifierCollection;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApi\Resources\ResourceObjectCollection;

class ExportService
{
    /**
     * Undocumented variable
     *
     * @var AliasesService
     */
    private $alias;

    /**
     * Class constructor
     *
     * @param AliasesService $aliasesService
     */
    public function __construct(AliasesService $aliasesService)
    {
        $this->alias = $aliasesService;
    }

    /**
     * Undocumented function
     *
     * @param Collection|Model|null $obj
     * @param Request|null          $request
     *
     * @return ResourceObjectCollection|ResourceObject|null
     */
    public function exportAsResource($obj, $request = null)
    {
        return $this->export($obj, false, $request);
    }

    /**
     * Undocumented function
     *
     * @param Collection|Model|null $obj
     * @param Request|null          $request
     *
     * @return ResourceIdentifierCollection|ResourceIdentifier|null
     */
    public function exportAsResourceIdentifier($obj, $request = null)
    {
        return $this->export($obj, true, $request);
    }

    /**
     * Undocumented function
     *
     * @param Model $model
     *
     * @return ResourceObject|null
     */
    public function exportSingleResource($model)
    {
        return $this->exportSingle($model, 'getResourceClassName');
    }

    /**
     * Undocumented function
     *
     * @param Model $model
     *
     * @return ResourceIdentifier|null
     */
    public function exportSingleResourceIdentifier($model)
    {
        return $this->exportSingle($model, 'getResourceIdentifierClassName');
    }

    /**
     * Undocumented function
     *
     * @param Collection $collection
     * @param Request    $request
     *
     * @return ResourceObjectCollection
     */
    public function exportCollectionOfResource($collection, Request $request)
    {
        return $this->exportCollection($collection, $request, 'getResourceCollectionClassName');
    }

    /**
     * Undocumented function
     *
     * @param Collection $collection
     * @param Request    $request
     *
     * @return ResourceIdentifierCollection
     */
    public function exportCollectionOfResourceIdentifier($collection, Request $request)
    {
        return $this->exportCollection($collection, $request, 'getResourceIdentifierCollectionClassName');
    }

    /**
     * Undocumented function
     *
     * @param Model    $model
     * @param Request  $request
     * @param Callable $fn
     *
     * @return ResourceObject|ResourceIdentifier|null
     */
    private function exportSingle($model, $fn)
    {
        // Gets resource class name
        $resourceName = $this->alias->{$fn}($model);

        // Creates resource
        $resource = call_user_func([$resourceName, 'make'], $model);

        return $resource;
    }

    /**
     * Undocumented function
     *
     * @param Collection $collection
     * @param Request    $request
     * @param Callable   $fn
     *
     * @return ResourceObjectCollection|ResourceIdentifierCollection
     */
    private function exportCollection($collection, Request $request, $fn)
    {
        // Gets resource class name
        $resourceName = $this->alias->{$fn}($request);

        // Creates resource
        $resource = call_user_func([$resourceName, 'make'], $collection);

        return $resource;
    }

    /**
     * Undocumented function
     *
     * @param Collection|Model|null $obj
     * @param bool                  $asResourceIdentifier
     * @param Request               $request
     *
     * @return ResourceObjectCollection|ResourceIdentifierCollection|ResourceObject|ResourceIdentifier|null
     */
    private function export($obj, bool $asResourceIdentifier, $request = null)
    {
        $isSingle = (($obj === null) || is_a($obj, Model::class));
        $fn = 'export';
        $fn .= $isSingle ? 'Single' : 'CollectionOf';
        $fn .= $asResourceIdentifier ? 'ResourceIdentifier' : 'Resource';

        return $this->{$fn}($obj, $request);
    }
}
