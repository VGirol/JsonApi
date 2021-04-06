<?php

namespace VGirol\JsonApi\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotNullable implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return null !== $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must not be null.';
    }
}
