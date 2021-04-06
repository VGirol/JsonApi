<?php

namespace VGirol\JsonApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApiConstant\Members;

trait CanStoreResource
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param string  $routeKey
     * @param mixed   $parentId
     *
     * @return JsonResponse
     */
    protected function storeAndReply(Request $request, string $routeKey, $parentId = null): JsonResponse
    {
        // Creates and validate specific FormRequest
        $request = $this->validateData($request);

        // Stores resource
        $resource = DB::transaction(function () use ($request, $routeKey, $parentId) {
            return $this->storeSingle($request, $routeKey, $parentId);
        }, config('jsonapi.transactionAttempts'));

        // Fills response's content
        $return204 = $request->has(Members::DATA . '.' . Members::ID) && !$resource->haveChanged();
        $response = $return204 ? $this->responseService->noContent() : $this->responseService->created($resource);

        // Adds Location header
        if (config('jsonapi.creationAddLocationHeader')) {
            $response->header('Location', $resource->getResourceSelfLink($request));
        }

        return $response;
    }

    /**
     * Store a single resource
     *
     * @param Request $request
     * @param string  $routeKey
     * @param int     $parentId
     *
     * @return ResourceObject
     */
    protected function storeSingle(Request $request, string $routeKey, $parentId = null)
    {
        // Gets the data
        // $data = $request->validated(Members::DATA);
        $data = $request->input(Members::DATA);

        // store resource
        $model = $this->storeService->saveModel($data, $routeKey);

        // Looks for relationships
        $this->relationshipService->saveAll($data, $model);

        // Attach created resource (if related) to parent
        if ($parentId !== null) {
            // Get relation instance
            $relation = $this->fetchService->getRelationFromRequest($request, $parentId, $routeKey);

            // Attach resource to parent
            $this->relationshipService->create($relation, $model);
        }

        // Get model for response (with required include or fields)
        $requiredModel = $this->fetchService->getRequiredModel($request, $model->getKey(), $model);

        // Creates resource
        $resource = $this->exportService->exportSingleResource($requiredModel);

        return $resource;
    }
}
