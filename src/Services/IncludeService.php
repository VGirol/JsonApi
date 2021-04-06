<?php

namespace VGirol\JsonApi\Services;

use VGirol\JsonApi\Tools\DotArray;

class IncludeService extends AbstractService
{
    protected function getConfigKey(): string
    {
        return 'include';
    }

    protected function parseParameters($request)
    {
        return $request->getIncludes();
    }

    public function queryIsValid(): bool
    {
        return true;
    }

    public function getQueryParameter(): array
    {
        return $this->hasQuery() ? [$this->getConfigKey() => DotArray::toDotKeys($this->parameters())->implode(',')] : [];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function allowedIncludes(): array
    {
        return DotArray::toDotKeys($this->parameters())->toArray();
    }
}
