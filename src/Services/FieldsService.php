<?php

namespace VGirol\JsonApi\Services;

class FieldsService extends AbstractService
{
    protected function getConfigKey(): string
    {
        return 'fields';
    }

    protected function parseParameters($request)
    {
        return $request->getFields();
    }

    public function queryIsValid(): bool
    {
        return true;
    }

    public function getQueryParameter(): array
    {
        return $this->hasQuery() ? [$this->getConfigKey() => $this->parameters()->map(
            function ($item) {
                return implode(',', $item);
            }
        )->toArray()] : [];
    }

    /**
     * Undocumented function
     *
     * @param  string  $resourceType
     *
     * @return array
     */
    public function allowedFields(string $resourceType): array
    {
        return array_map(
            'strtolower',
            jsonapiModel()->getVisible(
                jsonapiAliases()->getModelClassName($resourceType)
            )
        );
    }
}
