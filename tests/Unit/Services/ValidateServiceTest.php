<?php

namespace VGirol\JsonApi\Tests\Unit\Services;

use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Services\ValidateService;
use VGirol\JsonApi\Tests\CanCreateRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\PhpunitException\SetExceptionsTrait;

class ValidateServiceTest extends TestCase
{
    use SetExceptionsTrait;
    use CanCreateRequest;

    /**
     * @test
     */
    public function validateRequestStructure()
    {
        $content = [
            'data' => [
                'type' => 'photo',
                'attributes' => [
                    'attr' => 'value'
                ]
            ]
        ];
        $request = $this->createRequest('/', 'POST', $content);
        $service = new ValidateService();

        $service->validateRequestStructure($request);

        PHPUnit::assertTrue(true);
    }

    /**
     * @test
     */
    public function validateRequestStructureFail()
    {
        $content = [
            'data' => [
                'type' => 'photo',
                'attributes' => [
                    '+attr' => 'value'
                ]
            ]
        ];
        $request = $this->createRequest('/', 'POST', $content);
        $service = new ValidateService();

        $this->setFailure(JsonApiException::class);

        $service->validateRequestStructure($request);
    }
}
