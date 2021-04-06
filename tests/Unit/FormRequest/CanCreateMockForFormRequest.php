<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use VGirol\FormRequestTester\TestFormRequests;

trait CanCreateMockForFormRequest
{
    use TestFormRequests;

    private $formRequestAliases;

    protected function setUpForAbstractFormRequest(): void
    {
        $groups = $this->aliasGroups();

        $this->buildRoutes($groups);

        config()->set(
            'jsonapi-alias.groups',
            \array_values($groups)
        );
    }

    protected function formRequestAlias($group, $key)
    {
        $groups = $this->aliasGroups();
        return $groups[$group][$key];
    }

    protected function getFormRequestMockType($group = 'main'): string
    {
        return $this->formRequestAlias($group, 'type');
    }

    protected function getFormRequestMockRoute($group = 'main'): string
    {
        return $this->formRequestAlias($group, 'route');
    }

    private function aliasGroups()
    {
        return [
            'main' => [
                'type' => 'main',
                'route' => 'mainRoute',
                'model' => 'VGirol\JsonApi\Tests\Unit\FormRequest\ModelMock'
            ],
            'related' => [
                'type' => 'related',
                'route' => 'relatedRoute',
                'model' => 'VGirol\JsonApi\Tests\Unit\FormRequest\ModelMock'
            ]
        ];
    }
}
