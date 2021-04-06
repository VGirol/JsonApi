<?php

namespace VGirol\JsonApi\Tests\Tools\Requests;

use VGirol\JsonApi\Requests\ResourceFormRequest;

class TagFormRequest extends ResourceFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'TAGS_NAME' => [
                'required',
                'string',
                'max:255'
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
