<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use Illuminate\Validation\Rule;
use PHPUnit\Framework\Assert as PHPUnit;
use VGirol\JsonApi\Requests\ResourceFormRequest;
use VGirol\JsonApi\Tests\TestCase;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

class ResourceFormRequestTest extends TestCase
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

        // Create mock for ResourceFormRequest class
        $mock = $this->createFormRequestMock(
            ResourceFormRequest::class,
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
        $id = '6';

        return [
            'with custom rules (POST)' => [
                'POST',
                $this->getFormRequestMockRoute(),
                [
                    'attr1' => 'required|string',
                    'attr2' => 'string',
                    'attr3' => Rule::in('test')
                ],
                [
                    Members::DATA . '.' . Members::TYPE => [
                        // 'required',
                        // 'string',
                        Rule::in($this->getFormRequestMockType())
                    ],
                    Members::DATA . '.' . Members::ID => [
                        // 'string'
                    ],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr1' => ['required', 'string'],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr2' => ['string'],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr3' => [Rule::in('test')]
                ]
            ],
            'with custom rules (PATCH)' => [
                'PATCH',
                $this->getFormRequestMockRoute() . '/' . $id,
                [
                    'attr1' => 'required|string',
                    'attr2' => 'string|unique:main_table,FIELD',
                    'attr3' => Rule::in('test')
                ],
                [
                    Members::DATA . '.' . Members::TYPE => [
                        // 'required',
                        // 'string',
                        Rule::in($this->getFormRequestMockType())
                    ],
                    Members::DATA . '.' . Members::ID => [
                        // 'string',
                        // 'required',
                        Rule::in($id)
                    ],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr1' => ['string'],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr2' => [
                        'string',
                        (Rule::unique('main_table', 'FIELD'))->ignore($id, 'MAIN_ID')
                    ],
                    Members::DATA . '.' . Members::ATTRIBUTES . '.attr3' => [Rule::in('test')]
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function preparedMessagesReturnsAnArray()
    {
        // Creates a form
        $form = [
            Members::DATA => [
                Members::TYPE => $this->getFormRequestMockType(),
                Members::ATTRIBUTES => []
            ]
        ];

        // Create mock for ResourceFormRequest class
        $mock = $this->createFormRequestMock(
            ResourceFormRequest::class,
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
            ->will($this->returnValue([
                Members::DATA . '.' . Members::ATTRIBUTES . '.attr2.string' => 'error message'
            ]));
        $mock->expects($this->once())
            ->method('rules')
            ->will($this->returnValue([
                'attr1' => 'unique:TABLE,FIELD',
                'attr2' => 'string'
            ]));

        // Execute tested method
        $messages = call_user_func([$mock, 'preparedMessages']);

        // Create expected value
        $expected = [
            Members::DATA . '.' . Members::TYPE . '.required' =>
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT,
            Members::DATA . '.' . Members::ID . '.required' =>
                JsonApiStructureMessages::RESOURCE_ID_MEMBER_IS_ABSENT,
            Members::DATA . '.' . Members::TYPE . '.in' => '(409) ' . trans('validation.in'),
            Members::DATA . '.' . Members::ATTRIBUTES . '.attr1.unique' => '(409) ' . trans('validation.unique'),
            Members::DATA . '.' . Members::ATTRIBUTES . '.attr2.string' => 'error message'
        ];

        // Assert
        PHPUnit::assertIsArray($messages);
        PHPUnit::assertNotEmpty($messages);
        PHPUnit::assertEquals($expected, $messages);
    }
}
