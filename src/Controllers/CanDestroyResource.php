<?php

namespace VGirol\JsonApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Resources\ResourceObject;

trait CanDestroyResource
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
    protected function destroyAndReply(Request $request, string $routeKey, $id): JsonResponse
    {
        // Destroys the model
        $resource = DB::transaction(function () use ($routeKey, $id) {
            return $this->destroySingle($routeKey, $id);
        }, config('jsonapi.transactionAttempts'));

        // Fill response's content
        $return204 = !$resource->hasDocumentMeta($request);
        $response = $return204 ? $this->responseService->noContent() :
            $this->responseService->ok($resource->getDocumentMeta($request));

        return $response;
    }

    /**
     * Destroy a single resource
     *
     * @param string $routeKey
     * @param mixed  $id
     *
     * @return ResourceObject
     */
    protected function destroySingle(string $routeKey, $id)
    {
        // Deletes model
        $model = $this->storeService->deleteModel($routeKey, $id);

        // Creates resource
        $resourceName = jsonapiAliases()->getResourceClassName($model);
        $resource = \call_user_func([$resourceName, 'make'], $model);

        return $resource;
    }
}
