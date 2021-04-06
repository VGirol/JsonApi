<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use Illuminate\Validation\Rule;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Requests\RelationshipFormRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;

class RelationshipFormRequestTest extends TestCase
{
    use RequestSetUp;

    /**
     * @test
     */
    public function preparedRulesReturnsDefaultRules()
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ID => '123'
            ]
        ];

        // Create ResourceIdentifierFormRequest object
        $obj = $this->createFormRequest(
            RelationshipFormRequest::class,
            $form,
            [
                'method' => 'POST',
                'route' => $this->getFormRequestMockRoute()
            ]
        );

        // Execute tested method
        $rules = $obj->preparedRules();

        // Assert
        PHPUnit::assertIsArray($rules);
        PHPUnit::assertNotEmpty($rules);

        $expected = [
            Members::DATA . '.' . Members::TYPE => [
                Rule::in($this->getFormRequestMockType())
            ],
            Members::DATA . '.' . Members::ID => [
                // 'string'
            ]
        ];
        PHPUnit::assertEquals($expected, $rules);
    }
}
