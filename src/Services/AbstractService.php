<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use VGirol\JsonApi\Contracts\ServiceContract;

abstract class AbstractService implements ServiceContract
{
    /**
     * @var Collection
     */
    private $parameters;

    /**
     * Undocumented function
     *
     * @return string
     */
    abstract protected function getConfigKey(): string;

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return Collection
     */
    abstract protected function parseParameters($request);

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param boolean $force
     *
     * @return $this
     */
    public function parseRequest($request = null, $force = false)
    {
        $request = $request ?? request();
        if (is_null($this->parameters) || $force) {
            $this->parameters = $this->parseParameters($request);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function hasQuery($request = null): bool
    {
        return $this->parseRequest($request)->parameters->isNotEmpty();
    }

    /**
     * Undocumented function
     *
     * @return Collection
     */
    public function parameters()
    {
        return $this->parameters;
    }

    /**
     * Undocumented function
     *
     * @param string|int $key
     * @param mixed      $default
     *
     * @return mixed
     */
    public function value($key, $default = null)
    {
        return isset($this->parameters) ? $this->parameters->get($key, $default) : $default;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getQueryParameter(): array
    {
        return $this->hasQuery() ? [$this->getConfigKey() => $this->parameters()->toArray()] : [];
    }

    /**
     * Undocumented function
     *
     * @param string $separator
     *
     * @return string
     */
    public function implode(string $separator = ', '): string
    {
        return $this->parameters->join($separator);
    }

    public function allowedByServer(): bool
    {
        return config("jsonapi.{$this->getConfigKey()}.allowed", true);
    }

    public function allowedForRoute($request = null): bool
    {
        $request = $request ?? request();

        return $request->routeIs(config("jsonapi.{$this->getConfigKey()}.routes"));
    }

    public function allowed($request = null): bool
    {
        return $this->allowedByServer() && $this->allowedForRoute($request);
    }
}
