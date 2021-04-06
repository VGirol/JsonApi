<?php

namespace VGirol\JsonApi\Tests\Tools\Requests;

use VGirol\JsonApi\Requests\ResourceFormRequest;

class CommentFormRequest extends ResourceFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'COMMENT_TEXT' => [
                'required',
                'string'
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
