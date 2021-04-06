<?php

namespace VGirol\JsonApi\Tests;

use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

trait CanCreateRequest
{

    /**
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data

     */
    protected function createRequest(
        $uri,
        $method,
        $parameters = [],
        $cookies = [],
        $files = [],
        $server = [],
        $content = null
    ) {
        $server = array_merge(['REQUEST_URI' => $uri], $server);
        $newServer = array_combine(
            array_map(
                function ($key, $value) {
                    return (strtoupper($key) === $key) ? $key : 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                },
                array_keys($server),
                $server
            ),
            $server
        );

        $request = Request::create($uri, $method, $parameters, $cookies, $files, $newServer, $content);

        $request->setRouteResolver(function () use ($request) {
            return app('router')->getRoutes()->match($request);
        });

        App::instance(IlluminateRequest::class, $request);

        return $request;
    }
}
