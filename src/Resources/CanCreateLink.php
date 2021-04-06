<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

trait CanCreateLink
{
    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return string
     */
    public function getDocumentSelfLink($request): string
    {
        // $url = rawurldecode($request->fullUrl());
        // $elements = explode('?', $url);
        // if (count($elements) > 1) {
        //     $elements[1] = collect(explode('&', $elements[1]))
        //         ->map(function ($item) {
        //             return collect(explode('=', $item))->map(function ($item) {
        //                 return rawurlencode($item);
        //             })->join('=');
        //         })->join('&');
        //     $url = implode('?', $elements);
        // }

        // return $url;
        return $request->fullUrl();
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param bool    $withQuery
     *
     * @return string|null
     */
    public function getCollectionSelfLink($request, $withQuery = false): ?string
    {
        return $this->getUrl(
            "{$this->aliases->getResourceRoute($request)}.index",
            $withQuery ? $request->query() : []
        );
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return string|null
     */
    public function getDocumentRelatedLink($request): ?string
    {
        return $this->getUrl(
            "{$this->aliases->getParentRoute($request)}.related.index",
            [
                'parentId' => $request->route('parentId'),
                'relationship' => $request->route('relationship')
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param string $model
     *
     * @return string
     */
    public function getResourceSelfLink($model): ?string
    {
        return $this->getUrl(
            "{$this->aliases->getResourceRoute($model)}.show",
            [
                'id' => $this->getId()
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param Model $model Parent model
     * @param string $relationshipName
     *
     * @return string|null
     */
    public function getRelationshipSelfLink($model, string $relationshipName): ?string
    {
        return $this->getUrl(
            "{$this->aliases->getParentRoute($model)}.relationship.index",
            [
                'parentId' => $model->getKey(),
                'relationship' => $relationshipName
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param Model $model Parent model
     * @param string $relationshipName
     *
     * @return string|null
     */
    public function getRelationshipRelatedLink($model, string $relationshipName): ?string
    {
        return $this->getUrl(
            "{$this->aliases->getParentRoute($model)}.related.index",
            [
                'parentId' => $model->getKey(),
                'relationship' => $relationshipName
            ]
        );
    }

    /**
     * Undocumented function
     *
     * @param string $routeName
     * @param array  $params
     *
     * @return string|null
     */
    private function getUrl(string $routeName, array $params = []): ?string
    {
        return Route::has($routeName) ? route($routeName, $params) : null;
    }
}
