<?php

namespace VGirol\JsonApi\Tests\Tools\Requests;

use VGirol\JsonApi\Requests\ResourceFormRequest;

class PriceFormRequest extends ResourceFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'PRICE_VALUE' => [
                'required',
                'numeric'
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
