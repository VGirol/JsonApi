<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use VGirol\JsonApi\Tests\UsesTools;
use VGirol\JsonApiStructure\Exception\ValidationException;
use VGirol\PhpunitException\SetExceptionsTrait;

trait RequestSetUp
{
    use CanCreateMockForFormRequest;
    use SetExceptionsTrait;
    use UsesTools;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpToolsDB();
        $this->setUpForAbstractFormRequest();
    }

    private function setValidationFailure(?string $message = null, $code = null): void
    {
        $this->setFailure(ValidationException::class, $message, $code);
    }
}
