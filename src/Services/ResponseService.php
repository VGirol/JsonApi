<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use VGirol\JsonApi\Resources\ErrorResourceCollection;

class ResponseService
{
    /**
     * Undocumented function
     *
     * @param JsonResource|mixed|null $content
     *
     * @return JsonResponse
     */
    public function ok($content): JsonResponse
    {
        return $this->createResponse(JsonResponse::HTTP_OK, $content);
    }

    /**
     * Undocumented function
     *
     * @param JsonResource|mixed|null $content
     *
     * @return JsonResponse
     */
    public function created($content): JsonResponse
    {
        return $this->createResponse(JsonResponse::HTTP_CREATED, $content);
    }

    /**
     * Undocumented function
     *
     * @return JsonResponse
     */
    public function noContent(): JsonResponse
    {
        return $this->createResponse(JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Undocumented function
     *
     * @return JsonResponse
     */
    public function createErrorResponse(): JsonResponse
    {
        $resource = new ErrorResourceCollection(jsonapiError()->errors());

        return $this->createResponse($resource->getStatusCode(), $resource);
    }

    /**
     * Undocumented function
     *
     * @param integer                 $code
     * @param JsonResource|mixed|null $content
     *
     * @return JsonResponse
     */
    public function createResponse(int $code, $content = null): JsonResponse
    {
        if (is_null($content)) {
            return response()->json(null, $code);
        }
        if (is_a($content, 'Illuminate\Http\Resources\Json\JsonResource')) {
            return $content->response()->setStatusCode($code);
        }

        return new JsonResponse($content, $code);
    }
}
