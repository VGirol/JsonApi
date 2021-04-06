<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use VGirol\JsonApi\Exceptions\JsonApi400Exception;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Exceptions\JsonApi404Exception;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Model\Related;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Services\FieldsService;
use VGirol\JsonApi\Services\FilterService;
use VGirol\JsonApi\Services\IncludeService;
use VGirol\JsonApi\Services\PaginationService;
use VGirol\JsonApi\Services\SortService;
use VGirol\JsonApiConstant\Members;

class FetchService
{
    /**
     * Undocumented variable
     *
     * @var SortService
     */
    protected $sort;

    /**
     * Undocumented variable
     *
     * @var FilterService
     */
    protected $filter;

    /**
     * Undocumented variable
     *
     * @var FieldsService
     */
    protected $fields;

    /**
     * Undocumented variable
     *
     * @var IncludeService
     */
    protected $include;

    /**
     * Undocumented variable
     *
     * @var AliasesService
     */
    protected $alias;

    /**
     * Undocumented variable
     *
     * @var PaginationService
     */
    protected $pagination;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->alias = jsonapiAliases();
        $this->sort = jsonapiSort();
        $this->filter = jsonapiFilter();
        $this->fields = jsonapiFields();
        $this->include = jsonapiInclude();
        $this->pagination = jsonapiPagination();
    }

    /**
     * Find a single resource
     *
     * @param Request        $request
     * @param string|Builder $baseQuery
     * @param mixed          $id
     *
     * @return Model
     * @throws JsonApiException
     * @throws ModelNotFoundException
     */
    public function find(Request $request, $baseQuery, $id)
    {
        if (
            $this->sort->hasQuery($request)
            || $this->filter->hasQuery($request)
            || $this->pagination->hasQuery($request)
        ) {
            throw new JsonApiException(Messages::ERROR_FETCHING_SINGLE_WITH_NOT_ALLOWED_QUERY_PARAMETERS);
        }

        // Create QueryBuilder object for sorting, filtering, including ...
        $queryBuilder = $this->getQueryBuilder($request, $baseQuery);

        return $queryBuilder->findOrFail($id);
    }

    /**
     * Get a collection of resources
     *
     * @param Request        $request
     * @param string|Builder $baseQuery
     *
     * @return EloquentCollection
     * @throws JsonApiException
     */
    public function get(Request $request, $baseQuery)
    {
        // Create QueryBuilder object for sorting, filtering, including ...
        $queryBuilder = $this->getQueryBuilder($request, $baseQuery);

        // Get total items before pagination
        $itemTotal = $queryBuilder->count();
        $this->pagination->setTotalItem($itemTotal);

        if ($this->pagination->allowed()) {
            // Pagination method
            $method_name = config('json-api-paginate.method_name');

            // Paginate collection
            $builder = call_user_func([$queryBuilder, $method_name]);

            $collection = $builder->getCollection();
        } else {
            $collection = $queryBuilder->get();
        }

        $collection->itemTotal = $itemTotal;

        return $collection;
    }

    /**
     * Find a single related resource
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     * @param mixed   $id
     *
     * @return Model
     * @throws JsonApi403Exception
     * @throws ModelNotFoundException
     */
    public function findRelated(Request $request, $parentId, string $relationship, $id)
    {
        // return $this->getRelationFromRequest($request, $parentId, $relationship)
        //     ->getQuery()
        //     ->findOrFail($id);
        return $this->getQueryBuilder(
            $request,
            $this->getRelationFromRequest($request, $parentId, $relationship)
                ->getQuery()
        )
            ->findOrFail($id);
    }

    /**
     * Gets one or more related resources.
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     * @param boolean $asResourceIdentifier
     *
     * @return EloquentCollection|Model|null
     * @throws JsonApiException
     * @throws JsonApi400Exception
     * @throws JsonApi403Exception
     * @throws ModelNotFoundException
     */
    public function getRelated(Request $request, $parentId, string $relationship)
    {
        // Gets the relation and its query builder
        $relation = $this->getRelationFromRequest($request, $parentId, $relationship);
        $query = $relation->getQuery();

        // Creates resource
        if ($relation->isToMany()) {
            $resource = $this->get($request, $query);
        } else {
            if (jsonapiSort()->hasQuery()) {
                throw new JsonApi400Exception(Messages::SORTING_IMPOSSIBLE_FOR_TO_ONE_RELATIONSHIP);
            }
            $id = $query->count() ? $relation->getResults()->getKey() : null;
            $resource = ($id !== null) ? $this->find($request, $query, $id) : null;
        }

        return $resource;
    }

    /**
     * Gets all related resources specified in request data
     *
     * @param array $data
     *
     * @return Collection|Related
     * @throws JsonApi404Exception
     */
    public function getRelatedFromRequestData(array $data)
    {
        return $this->extractRelated($data);
    }

    /**
     * Undocumented function
     *
     * @param Collection|Model|Related|array|null $data
     *
     * @return Collection|Related
     * @throws JsonApi404Exception
     */
    public function extractRelated($data)
    {
        $isSingle = $this->isSingle($data);

        if ($isSingle) {
            $data = [$data];
        }

        $collection = Collection::make($data)->map(
            function ($item) {
                return $this->getRelatedInstance($item);
            }
        );

        return $isSingle ? $collection->first() : $collection;
    }

    /**
     * Get a Relation object.
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     *
     * @return Relation
     * @throws JsonApi403Exception
     * @throws ModelNotFoundException
     */
    public function getRelationFromRequest(Request $request, $parentId, string $relationship)
    {
        return $this->getRelationFromModel(
            $this->findParent($request, $parentId),
            $relationship
        );
    }

    /**
     * Get a Relation object.
     *
     * @param Model  $model
     * @param string $relationship
     *
     * @return Relation
     * @throws JsonApi403Exception
     */
    public function getRelationFromModel($model, string $relationship)
    {
        // Get the relationship
        if (!\method_exists($model, $relationship)) {
            throw new JsonApi403Exception(sprintf(Messages::NON_EXISTENT_RELATIONSHIP, $relationship));
        }

        return $model->$relationship();
    }

    /**
     * Get the model required to fill the server response
     *
     * @param Request $request
     * @param mixed   $id
     * @param Model   $model
     *
     * @return Model
     */
    public function getRequiredModel(Request $request, $id, $model)
    {
        // Find model
        $requiredModel = $this->find(
            $request,
            $this->alias->getModelClassName($request),
            $id
        );

        // Add tag indicating if automatic changes have been made
        $requiredModel->automaticChanges = (\count(\array_diff_assoc(
            ($request->method() == 'POST') ? $model->attributesToArray() : $model->getChanges(),
            $request->input(Members::DATA . '.' . Members::ATTRIBUTES, [])
        )) !== 0);

        $requiredModel->makeHidden('automaticChanges');

        return $requiredModel;
    }

    /**
     * Find the parent of a related resource
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findParent(Request $request, $id)
    {
        return \call_user_func(
            [$this->alias->getParentClassName($request), 'findOrFail'],
            $id
        );
    }

    protected function getAllowedFilters(Request $request): array
    {
        return $this->filter->allowedFilters();
    }

    /**
     * Get an instance of the query builder
     *
     * @param Request        $request
     * @param string|Builder $baseQuery
     *
     * @return QueryBuilder
     * @throws JsonApiException
     */
    private function getQueryBuilder(Request $request, $baseQuery): QueryBuilder
    {
        // Create QueryBuilder object for sorting, filtering, ...
        $queryBuilder = QueryBuilder::for($baseQuery, $request);

        $table = $queryBuilder->getModel()->getTable();

        // Sorting
        if (!$this->sort->queryIsValid($table)) {
            throw new JsonApiException(sprintf(Messages::SORTING_BAD_FIELD, $this->sort->implode()));
        }
        $queryBuilder->allowedSorts($this->sort->allowedSorts());

        // Filtering
        $queryBuilder->allowedFilters($this->getAllowedFilters($request));

        // Selecting fields
        $queryBuilder->allowedFields($this->fields->allowedFields($this->alias->getResourceType($request)));
        if ($this->fields->hasQuery()) {
            $keyName = Str::lower($queryBuilder->getModel()->getKeyName());
            if (!Arr::has($this->fields->getQueryParameter(), $keyName)) {
                // Add key name to the selected fields
                $queryBuilder->addSelect($table . '.' . $keyName);
            }
        }

        // Including relationships
        $queryBuilder->allowedIncludes($this->include->allowedIncludes());

        return $queryBuilder;
    }

    /**
     * Undocumented function
     *
     * @param Related|Model|array $item
     *
     * @return Related
     * @throws JsonApi404Exception
     */
    private function getRelatedInstance($item): Related
    {
        if (\is_object($item) && \is_a($item, Related::class)) {
            return $item;
        }

        $model = $this->getModelInstance($item);

        if ($model === null) {
            throw new JsonApi404Exception(
                sprintf(Messages::UPDATING_REQUEST_RELATED_NOT_FOUND, $item[Members::TYPE])
            );
        }

        $rel = new Related($model, $item[Members::META] ?? []);

        return $rel;
    }

    /**
     * Undocumented function
     *
     * @param Model|array $item
     *
     * @return Model
     */
    private function getModelInstance($item)
    {
        if (\is_object($item) && \is_a($item, Model::class)) {
            return $item;
        }

        return call_user_func(
            [$this->alias->getModelClassName($item[Members::TYPE]), 'make']
        )->find($item[Members::ID]);
    }

    /**
     * Undocumented function
     *
     * @param Collection|Model|Related|array|null $data
     *
     * @return boolean
     */
    private function isSingle($data): bool
    {
        $isSingle = false;

        if (\is_array($data)) {
            $isSingle = Arr::isAssoc($data);
        }
        if (\is_object($data) && (\is_a($data, Model::class) || \is_a($data, Related::class))) {
            $isSingle = true;
        }

        return $isSingle;
    }
}
