<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\JsonResponse;
use VGirol\JsonApiConstant\Members;

class ErrorResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $meta = null;
        if (config('app.debug')) {
            if (method_exists($this->resource, 'getCode')) {
                $meta[Members::ERROR_CODE] = $this->resource->getCode();
            }
            if (method_exists($this->resource, 'getTrace')) {
                $meta['trace'] = array_slice($this->resource->getTrace(), 0, config('jsonapi.errorTraceLength'));
            }
        }

        $statusCode = $this->getStatusCode();
        $error = [
            Members::ERROR_STATUS => (string)$statusCode,
            Members::ERROR_TITLE => JsonResponse::$statusTexts[$statusCode],
            Members::ERROR_DETAILS => $this->resource->getMessage()
        ];
        if (!is_null($meta)) {
            $error[Members::META] = $meta;
        }

        return $error;
    }

    public function getStatusCode()
    {
        if (method_exists($this->resource, 'getStatusCode')) {
            $statusCode = $this->resource->getStatusCode();
        } elseif (property_exists($this->resource, Members::ERROR_STATUS)) {
            $statusCode = $this->resource->status;
        } else {
            $statusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $statusCode;
    }
}
