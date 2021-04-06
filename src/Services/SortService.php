<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Support\Facades\Schema;
use VGirol\JsonApi\Exceptions\JsonApiException;

class SortService extends AbstractService
{
    protected function getConfigKey(): string
    {
        return 'sort';
    }

    protected function parseParameters($request)
    {
        return $request->getSorts();
    }

    /**
     * @throws JsonApiException
     */
    public function queryIsValid(string $tableName = null): bool
    {
        if (is_null($tableName)) {
            throw new JsonApiException('Parameter "$tableName" can not be null.');
        }

        foreach ($this->parameters() as $item) {
            $sort = substr($item, 1);
            if (!Schema::hasColumn($tableName, $sort)) {
                return false;
            }
        }

        return true;
    }

    public function getQueryParameter(): array
    {
        return $this->hasQuery() ? [$this->getConfigKey() => $this->parameters()->map(
            function ($item) {
                return str_replace('+', null, $item);
            }
        )->implode(',')] : [];
    }

    public function allowedSorts(): array
    {
        return $this->parameters()->map(function ($include) {
            return str_replace(['+', '-'], '', $include);
        })->toArray();
    }
}
