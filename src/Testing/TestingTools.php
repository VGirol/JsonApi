<?php

namespace VGirol\JsonApi\Testing;

use Illuminate\Support\Str;
use VGirol\JsonApiAssert\Laravel\UseJsonapiTestResponse;

trait TestingTools
{
    use UseJsonapiTestResponse;

    public function jsonApi($method, $uri, array $data = [], array $headers = [])
    {
        $requestHeaders = ['Content-Type' => $this->getMediaType(), 'Accept' => $this->getMediaType()];
        $headers = array_merge($requestHeaders, $headers);

        return parent::json($method, $uri, $data, $headers);
    }

    protected function getMediaType()
    {
        return config('jsonapi.media-type');
    }

    protected function formatAttribute(string $attr): string
    {
        return str_replace('_', ' ', Str::snake($attr));
    }

    protected function replaceAttribute(string $str, string $attr): string
    {
        return str_replace(':attribute', $this->formatAttribute($attr), $str);
    }

    protected function refreshRouter()
    {
        // https://github.com/laravel/framework/issues/19020#issuecomment-409873471
        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();
    }

    protected function setConfigAliases($config, bool $reset)
    {
        // Set config
        $config = $reset ? $config : \array_merge(config()->get('jsonapi-alias.groups', []), $config);
        config()->set('jsonapi-alias.groups', $config);
    }
}
