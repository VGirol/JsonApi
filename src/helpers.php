<?php

use Illuminate\Http\Request;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApi\Services\ExceptionService;
use VGirol\JsonApi\Services\FieldsService;
use VGirol\JsonApi\Services\FilterService;
use VGirol\JsonApi\Services\IncludeService;
use VGirol\JsonApi\Services\ModelService;
use VGirol\JsonApi\Services\PaginationService;
use VGirol\JsonApi\Services\Service;
use VGirol\JsonApi\Services\SortService;

if (!function_exists('jsonapiAliases')) {
    /**
     * Undocumented function
     *
     * @return AliasesService
     */
    function jsonapiAliases(): AliasesService
    {
        return app()->make(AliasesService::class);
    }
}

if (!function_exists('jsonapiError')) {
    /**
     * Undocumented function
     *
     * @param \Throwable $e
     * @param boolean $check
     *
     * @return ExceptionService
     */
    function jsonapiError(\Throwable $e = null, $check = true): ExceptionService
    {
        $service = app()->make(ExceptionService::class);
        if (!is_null($e)) {
            $service->addException($e, $check);
        }

        return $service;
    }
}

if (!function_exists('jsonapiModel')) {
    /**
     * Undocumented function
     *
     * @return ModelService
     */
    function jsonapiModel(): ModelService
    {
        return app()->make(ModelService::class);
    }
}

if (!function_exists('jsonapiFields')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return FieldsService
     */
    function jsonapiFields(Request $request = null): FieldsService
    {
        return jsonapiService(FieldsService::class, $request);
    }
}

if (!function_exists('jsonapiFilter')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return FilterService
     */
    function jsonapiFilter(Request $request = null): FilterService
    {
        return jsonapiService(FilterService::class, $request);
    }
}

if (!function_exists('jsonapiInclude')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return IncludeService
     */
    function jsonapiInclude(Request $request = null): IncludeService
    {
        return jsonapiService(IncludeService::class, $request);
    }
}

if (!function_exists('jsonapiPagination')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param int|null $totalItem
     * @return PaginationService
     */
    function jsonapiPagination(Request $request = null, $totalItem = null): PaginationService
    {
        return jsonapiService(PaginationService::class, $request, $totalItem);
    }
}

if (!function_exists('jsonapiSort')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return SortService
     */
    function jsonapiSort(Request $request = null): SortService
    {
        return jsonapiService(SortService::class, $request);
    }
}

if (!function_exists('jsonapiService')) {
    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return Service
     */
    function jsonapiService(string $serviceName, Request $request = null, ...$args)
    {
        if (is_null($request)) {
            $request = request();
        }
        $service = resolve($serviceName);
        $service->parseRequest($request, false, ...$args);

        return $service;
    }
}
