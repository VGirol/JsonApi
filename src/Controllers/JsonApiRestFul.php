<?php

namespace VGirol\JsonApi\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use VGirol\JsonApi\Services\ExportService;
use VGirol\JsonApi\Services\FetchService;
use VGirol\JsonApi\Services\RelationshipService;
use VGirol\JsonApi\Services\ResponseService;
use VGirol\JsonApi\Services\StoreService;

trait JsonApiRestFul
{
    use CanDestroyResource;
    use CanStoreResource;
    use CanUpdateResource;
    use ManageRelationships;

    /**
     * Undocumented variable
     *
     * @var ExportService
     */
    protected $exportService;

    /**
     * Undocumented variable
     *
     * @var FetchService
     */
    protected $fetchService;

    /**
     * Undocumented variable
     *
     * @var RelationshipService
     */
    protected $relationshipService;

    /**
     * Undocumented variable
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Undocumented variable
     *
     * @var StoreService
     */
    protected $storeService;

    /**
     * Class constructor
     *
     * @param ExportService       $exportService
     * @param FetchService        $fetchService
     * @param RelationshipService $relationshipService
     * @param StoreService        $storeService
     *
     * @return void
     */
    public function __construct(
        ExportService $exportService,
        FetchService $fetchService,
        RelationshipService $relationshipService,
        ResponseService $responseService,
        StoreService $storeService
    ) {
        $this->exportService = $exportService;
        $this->fetchService = $fetchService;
        $this->relationshipService = $relationshipService;
        $this->responseService = $responseService;
        $this->storeService = $storeService;
    }

    /**
     * Returns a collection of resources.
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Retrieve model class name
        $modelName = jsonapiAliases()->getModelClassName($request);

        // Gets the collection
        $collection = $this->fetchService->get($request, $modelName);

        // Creates resource
        $resource = $this->exportService->exportCollectionOfResource($collection, $request);

        // Return response
        return $this->responseService->ok($resource);
    }

    /**
     * Returns the specified resource.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse
    {
        // Retrieve model class name
        $modelName = jsonapiAliases()->getModelClassName($request);

        // Loads model
        $model = $this->fetchService->find($request, $modelName, $id);

        // Creates resource
        $resource = $this->exportService->exportSingleResource($model);

        // Send response with content
        return $this->responseService->ok($resource);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Retrieve route key
        $routeKey = jsonapiAliases()->getResourceRoute($request);

        return $this->storeAndReply($request, $routeKey);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Gets the routeKey
        $routeKey = jsonapiAliases()->getResourceRoute($request);

        return $this->updateAndReply($request, $routeKey, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        // Gets the routeKey
        $routeKey = jsonapiAliases()->getResourceRoute($request);

        return $this->destroyAndReply($request, $routeKey, $id);
    }

    /**
     * Gets one or more related resources.
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     *
     * @return JsonResponse
     */
    public function relatedIndex(Request $request, $parentId, string $relationship): JsonResponse
    {
        // Fetch related model or collection
        $related = $this->fetchService->getRelated($request, $parentId, $relationship);

        // Creates resource
        $resource = $this->exportService->exportAsResource($related, $request);

        return $this->responseService->ok($resource);
    }

    /**
     * Returns the specified resource.
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     * @param mixed   $id

     * @return  JsonResponse
     */
    public function relatedShow(Request $request, $parentId, string $relationship, $id): JsonResponse
    {
        // Get the related model
        $model = $this->fetchService->findRelated($request, $parentId, $relationship, $id);

        // Creates resource
        $resource = $this->exportService->exportSingleResource($model);

        // Send response with content
        return $this->responseService->ok($resource);
    }

    /**
     * Creates one or more related resources.
     *
     * @param Request $request
     * @param int     $parentId
     * @param string  $relationship
     *
     * @return  JsonResponse
     */
    public function relatedStore(Request $request, $parentId, string $relationship): JsonResponse
    {
        return $this->storeAndReply($request, $relationship, $parentId);
    }

    /**
     * Updates a related resource.
     *
     * @param Request $request
     * @param int     $parentId
     * @param string  $relationship
     * @param mixed   $id
     *
     * @return  JsonResponse
     */
    public function relatedUpdate(Request $request, $parentId, string $relationship, $id): JsonResponse
    {
        $this->fetchService->findParent($request, $parentId);

        return $this->updateAndReply($request, $relationship, $id);
    }

    /**
     * Deletes a related resource.
     *
     * @param Request $request
     * @param int     $parentId
     * @param string  $relationship
     * @param mixed   $id
     *
     * @return  JsonResponse
     */
    public function relatedDestroy(Request $request, $parentId, string $relationship, $id): JsonResponse
    {
        $this->fetchService->findParent($request, $parentId);

        return $this->destroyAndReply($request, $relationship, $id);
    }

    /**
     * Creates and validate specific FormRequest
     *
     * @param Request $request
     *
     * @return FormRequest
     */
    protected function validateData(Request $request): FormRequest
    {
        return app(
            jsonapiAliases()->getFormRequestClassName($request)
        );
    }
}
