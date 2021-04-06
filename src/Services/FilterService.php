<?php

namespace VGirol\JsonApi\Services;

class FilterService extends AbstractService
{
    protected function getConfigKey(): string
    {
        return 'filter';
    }

    protected function parseParameters($request)
    {
        return $request->getFilters();
    }

    public function queryIsValid(): bool
    {
        return true;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function allowedFilters(): array
    {
        return $this->parameters()->keys()->toArray();
    }
}
