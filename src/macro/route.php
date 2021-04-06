<?php

use Illuminate\Support\Facades\Route;
use VGirol\JsonApi\Controllers\JsonApiController;

// Route macro
Route::macro(
    'jsonApiGet',
    function (string $uri, $action = null, $options = []) {
        $route = static::get($uri, $action);

        if (empty($options)) {
            $route->middleware('jsonapi');
        } else {
            $router = app('router');
            $middlewares = $router->getMiddlewareGroups()['jsonapi'];

            if (array_key_exists('except', $options)) {
                $except = is_array($options['except']) ? $options['except'] : [$options['except']];
                $middlewares = array_diff($middlewares, $except);
            }
            if (array_key_exists('only', $options)) {
                $only = is_array($options['only']) ? $options['only'] : [$options['only']];
                $middlewares = array_intersect($middlewares, $only);
            }
            if (array_key_exists('add', $options)) {
                $add = is_array($options['add']) ? $options['add'] : [$options['add']];
                $middlewares = array_merge($middlewares, $add);
            }

            $route->middleware($middlewares);
        }
    }
);

Route::macro(
    'jsonApiResource',
    function (string $name, ?string $controller = null, array $options = []) {
        $controller = static::jsonApiGetController($controller);

        if (!isset($options['parameters'])) {
            $options['parameters'] = [$name => 'id'];
        }

        static::apiResource($name, $controller, $options)->middleware('jsonapi');

        foreach (array_keys($options) as $opt) {
            $fn = 'jsonApi' . ucfirst(strtolower($opt));
            if (static::hasMacro($fn)) {
                call_user_func([Route::class, $fn], $name, $controller, $options);
            }
        }
    }
);

Route::macro(
    'jsonApiRelationships',
    function (string $name, ?string $controller = null, array $options = []) {
        if (!isset($options['relationships']) || ($options['relationships'] === true)) {
            $options['relationships'] = [];
        }
        if ($options['relationships'] === false) {
            return;
        }

        $controller = static::jsonApiGetController($controller);

        static::group(['middleware' => 'jsonapi'], function () use ($name, $controller) {
            // TODO :
            // if $options['relationships'] is true or empty array -> create generic routes
            // else -> create routes only for relationships provided in $options['relationships']

            // Relationships
            static::name("{$name}.relationship.index")->get(
                "{$name}/{parentId}/relationships/{relationship}",
                "{$controller}@relationshipIndex"
            );
            static::name("{$name}.relationship.store")->post(
                "{$name}/{parentId}/relationships/{relationship}",
                "{$controller}@relationshipStore"
            );
            static::name("{$name}.relationship.update")->match(
                ['put', 'patch'],
                "{$name}/{parentId}/relationships/{relationship}",
                "{$controller}@relationshipUpdate"
            );
            static::name("{$name}.relationship.destroy")->delete(
                "{$name}/{parentId}/relationships/{relationship}",
                "{$controller}@relationshipDestroy"
            );

            // Related resources
            static::name("{$name}.related.index")->get(
                "{$name}/{parentId}/{relationship}",
                "{$controller}@relatedIndex"
            );
            static::name("{$name}.related.store")->post(
                "{$name}/{parentId}/{relationship}",
                "{$controller}@relatedStore"
            );
            static::name("{$name}.related.show")->get(
                "{$name}/{parentId}/{relationship}/{id}",
                "{$controller}@relatedShow"
            );
            static::name("{$name}.related.update")->match(
                ['put', 'patch'],
                "{$name}/{parentId}/{relationship}/{id}",
                "{$controller}@relatedUpdate"
            );
            static::name("{$name}.related.destroy")->delete(
                "{$name}/{parentId}/{relationship}/{id?}",
                "{$controller}@relatedDestroy"
            );
        });
    }
);

Route::macro(
    'jsonApiGetController',
    function (string $controller = null): string {
        return $controller ?? '\\' . JsonApiController::class;
    }
);
