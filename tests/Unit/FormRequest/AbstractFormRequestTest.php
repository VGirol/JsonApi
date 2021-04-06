<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use Illuminate\Validation\Rule;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Requests\AbstractFormRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class AbstractFormRequestTest extends TestCase
{
    use RequestSetUp;

    /**
     * @test
     * @dataProvider preparedRulesReturnsAnArrayProvider
     */
    public function preparedRulesReturnsAnArray($method, $route, $returnValue, $expected)
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => []
            ]
        ];

        // Create mock for AbstractFormRequest class
        $mock = $this->createFormRequestMock(
            AbstractFormRequest::class,
            $form,
            [
                'method' => $method,
                'route' => $route
            ]
        );

        // Configure mock's methods
        $mock->expects($this->once())
            ->method('rules')
            ->will($this->returnValue($returnValue));

        // Execute tested method
        $rules = call_user_func([$mock, 'preparedRules']);

        // Assert
        PHPUnit::assertIsArray($rules);
        PHPUnit::assertNotEmpty($rules);
        PHPUnit::assertEquals($expected, $rules);
    }

    public function preparedRulesReturnsAnArrayProvider()
    {
        $default = [
            Members::DATA . '.' . Members::TYPE => [
                // 'required',
                // 'string',
                Rule::in($this->getFormRequestMockType())
            ],
            Members::DATA . '.' . Members::ID => [
                // 'string'
            ]
        ];

        return [
            'only default rules (POST)' => [
                'POST',
                $this->getFormRequestMockRoute(),
                [],
                $default
            ],
            'only default rules (PATCH)' => [
                'PATCH',
                $this->getFormRequestMockRoute() . '/6',
                [],
                array_merge_recursive(
                    $default,
                    [
                        Members::DATA . '.' . Members::ID => [
                            // 'required',
                            Rule::in(6)
                        ]
                    ]
                )
            ],
            'with custom rules' => [
                'POST',
                $this->getFormRequestMockRoute(),
                [
                    Members::DATA . '.' . Members::META => 'string'
                ],
                array_merge(
                    [
                        Members::DATA . '.' . Members::META => 'string'
                    ],
                    $default
                )
            ]
        ];
    }

    /**
     * @test
     */
    public function cacheSystemForRulesWorks()
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => []
            ]
        ];

        // Create mock for AbstractFormRequest class
        $mock = $this->createFormRequestMock(
            AbstractFormRequest::class,
            $form,
            [
                'method' => 'post',
                'route' => $this->getFormRequestMockRoute()
            ]
        );

        // Configure mock's methods
        $mock->expects($this->once())
            ->method('rules')
            ->will($this->returnValue([]));

        // Execute tested method
        call_user_func([$mock, 'preparedRules']);
        $rules = call_user_func([$mock, 'preparedRules']);

        // Create expected value
        $expected = [
            Members::DATA . '.' . Members::TYPE => [
                // 'required',
                // 'string',
                Rule::in($this->getFormRequestMockType())
            ],
            Members::DATA . '.' . Members::ID => [
                // 'string'
            ]
        ];

        // Assert
        PHPUnit::assertIsArray($rules);
        PHPUnit::assertNotEmpty($rules);
        PHPUnit::assertEquals($expected, $rules);
    }

    /**
     * @test
     * @dataProvider preparedMessagesReturnsAnArrayProvider
     */
    public function preparedMessagesReturnsAnArray($returnValue, $expected)
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => []
            ]
        ];

        // Create mock for AbstractFormRequest class
        $mock = $this->createFormRequestMock(
            AbstractFormRequest::class,
            $form,
            [
                'method' => 'POST',
                'route' => $this->getFormRequestMockRoute()
            ],
            [
                '',
                true,
                true,
                true,
                ['messages']
            ]
        );

        // Configure mock's methods
        $mock->expects($this->once())
            ->method('messages')
            ->will($this->returnValue($returnValue));

        // Execute tested method
        $messages = call_user_func([$mock, 'preparedMessages']);

        // Assert
        PHPUnit::assertIsArray($messages);
        PHPUnit::assertNotEmpty($messages);
        PHPUnit::assertEquals($expected, $messages);
    }

    public function preparedMessagesReturnsAnArrayProvider()
    {
        $default = [
            Members::DATA . '.' . Members::TYPE . '.required' =>
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT,
            Members::DATA . '.' . Members::ID . '.required' =>
                JsonApiStructureMessages::RESOURCE_ID_MEMBER_IS_ABSENT
        ];

        return [
            'only default messages' => [
                [],
                $default
            ],
            'with custom messages' => [
                [
                    Members::DATA . '.' . Members::META . '.string' => 'error message'
                ],
                array_merge(
                    [
                        Members::DATA . '.' . Members::META . '.string' => 'error message'
                    ],
                    $default
                )
            ]
        ];
    }

    /**
     * @test
     * @dataProvider validationSuccessProvider
     */
    public function validationSuccess($method, $route, $data)
    {
        // Creates a form
        $form = [
            Members::DATA => $data
        ];

        $this->mockFormRequest(
            AbstractFormRequest::class,
            $form,
            [
                'method' => $method,
                'route' => $route
            ]
        )
            ->assertValidationPassed();
    }

    public function validationSuccessProvider()
    {
        return [
            'main (PATCH)' => [
                'PATCH',
                $this->getFormRequestMockRoute() . '/2',
                [
                    Members::TYPE => $this->getFormRequestMockType(),
                    Members::ID => '2',
                    Members::ATTRIBUTES => [
                        'attr1' => 'first attribute'
                    ]
                ]
            ],
            'related (POST)' => [
                'POST',
                $this->getFormRequestMockRoute() . '/1/related',
                [
                    Members::TYPE => $this->getFormRequestMockType('related'),
                    Members::ATTRIBUTES => [
                        'attr1' => 'first attribute'
                    ]
                ]
            ],
            'relationship (POST)' => [
                'POST',
                $this->getFormRequestMockRoute() . '/1/relationships/related',
                [
                    Members::TYPE => $this->getFormRequestMockType('related'),
                    Members::ID => '123'
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function validationError()
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => 'bad_type',
                Members::ATTRIBUTES => [
                    'attr1' => 'first attribute'
                ]
            ]
        ];

        $this->mockFormRequest(
            AbstractFormRequest::class,
            $form,
            [
                'method' => 'POST',
                'route' => $this->getFormRequestMockRoute()
            ],
            function (string $formRequestType, array $args) {
                $mock = call_user_func_array(
                    [$this, 'getMockForAbstractClass'],
                    [
                        $formRequestType,
                        $args
                    ]
                );

                // Configure mock's methods
                $mock->expects($this->once())
                    ->method('rules')
                    ->will($this->returnValue([
                        Members::DATA . '.' . Members::ATTRIBUTES . '.attr1' => 'string',
                        Members::DATA . '.' . Members::ATTRIBUTES . '.attr2' => 'required'
                    ]));

                return $mock;
            }
        )
            ->assertValidationFailed()
            ->assertValidationErrors([
                Members::DATA . '.' . Members::TYPE,
                Members::DATA . '.' . Members::ATTRIBUTES . '.attr2'
            ])
            ->assertValidationErrorsMissing([
                Members::DATA . '.' . Members::ID,
                Members::DATA . '.' . Members::ATTRIBUTES . '.attr1'
            ])
            ->assertValidationMessages([
                'The ' . $this->formatAttribute(Members::DATA . '.' . Members::ATTRIBUTES . '.attr2') .
                    ' field is required.',
                'The selected ' . $this->formatAttribute(Members::DATA . '.' . Members::TYPE) . ' is invalid.'
            ]);
    }

    /**
     * @test
     */
    public function prepareForValidationFailed()
    {
        // Creates a form
        $meta = 'error';
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => [
                    'attr1' => 'first attribute',
                    'attr2' => 'second attribute'
                ]
            ],
            Members::META => $meta
        ];

        $this->setInvalidArgumentException(1, 'array', $meta);

        $this->mockFormRequest(
            AbstractFormRequest::class,
            $form,
            [
                'method' => 'POST',
                'route' => $this->getFormRequestMockRoute()
            ]
        )
            ->assertValidationFailed();
    }

    /**
     * @test
     */
    public function postValidationSuccessAndGetValidatedFields()
    {
        // Creates a form
        $attr1 = 'first attribute';
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => [
                    'attr1' => $attr1
                ]
            ]
        ];

        $this->mockFormRequest(
            AbstractFormRequest::class,
            $form,
            [
                'method' => 'POST',
                'route' => $this->getFormRequestMockRoute()
            ],
            function (string $formRequestType, array $args) {
                $mock = call_user_func_array(
                    [$this, 'getMockForAbstractClass'],
                    [
                        $formRequestType,
                        $args
                    ]
                );

                // Configure mock's methods
                $mock->expects($this->once())
                    ->method('rules')
                    ->will($this->returnValue([
                        Members::DATA . '.' . Members::ATTRIBUTES . '.attr1' => 'string'
                    ]));

                return $mock;
            }
        )
            ->assertValidationPassed();

        $fields = $this->currentFormRequest->validated();
        PHPUnit::assertIsArray($fields);
        PHPUnit::assertEquals($form, $fields);

        $field = $this->currentFormRequest->validated('data.attributes.attr1');
        PHPUnit::assertIsString($field);
        PHPUnit::assertEquals($attr1, $field);
    }
}
