<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateRange implements Rule
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
        $data_range = explode(' to ', $value);
        if (
            isset($data_range[0]) &&
            isset($data_range[1])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please select a start and end date';
    }
}
