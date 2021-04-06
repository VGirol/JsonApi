<?php

namespace VGirol\JsonApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Resources\ResourceObject;
use VGirol\JsonApiConstant\Members;

trait CanUpdateResource
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param string  $routeKey
     * @param mixed   $id
     *
     * @return JsonResponse
     */
    protected function updateAndReply(Request $request, string $routeKey, $id): JsonResponse
    {
        // Creates and validate specific FormRequest
        $request = $this->validateData($request);

        // Updates resource
        $resource = DB::transaction(function () use ($request, $routeKey, $id) {
            return $this->updateSingle($request, $routeKey, $id);
        }, config('jsonapi.transactionAttempts'));

        // Fill response's content
        $content = $resource->getContent($request);
        $response = ($content === null) ? $this->responseService->noContent() : $this->responseService->ok($content);

        return $response;
    }

    /**
     * Update a single resource in database
     *
     * @param Request $request
     * @param int     $id
     *
     * @return ResourceObject
     */
    protected function updateSingle(Request $request, $routeKey, $id)
    {
        // Gets the data
        // $data = $request->validated(Members::DATA);
        $data = $request->input(Members::DATA);

        // Update resource
        $model = $this->storeService->updateModel($data, $id, $routeKey);

        // Looks for relationships
        $this->relationshipService->updateAll($data, $model, true);

        // Get model for response (with required include or fields)
        $requiredModel = $this->fetchService->getRequiredModel($request, $id, $model);

        // Creates resource
        $resource = $this->exportService->exportSingleResource($requiredModel);

        return $resource;
    }
}
