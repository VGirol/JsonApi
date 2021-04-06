<?php

namespace VGirol\JsonApi\Contracts;

interface ServiceContract
{
    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return boolean
     */
    public function allowed($request = null): bool;

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function allowedByServer(): bool;

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return boolean
     */
    public function allowedForRoute($request = null): bool;

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return boolean
     */
    public function hasQuery($request = null): bool;

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function queryIsValid(): bool;

    /**
     * Undocumented function
     *
     * @param \Illuminate\Http\Request $request
     * @param boolean                  $force
     *
     * @return static
     */
    public function parseRequest($request = null, $force = false);

    /**
     * Undocumented function
     *
     * @param string $separator
     *
     * @return string
     */
    public function implode(string $separator = ', '): string;

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getQueryParameter(): array;
}
