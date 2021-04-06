<?php

namespace VGirol\JsonApi\Tests\Tools\Requests;

use VGirol\JsonApi\Requests\ResourceFormRequest;
use VGirol\JsonApi\Rules\NotNullable;

class PhotoFormRequest extends ResourceFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'PHOTO_TITLE' => [
                'required',
                'string',
                'max:255',
                'unique:photo,PHOTO_TITLE',
                new NotNullable()
            ],
            'PHOTO_SIZE' => 'integer',
            'PHOTO_DATE' => [
                'date',
                'nullable'
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
