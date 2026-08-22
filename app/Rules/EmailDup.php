<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\ClienteDelivery;

class EmailDup implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $cli = ClienteDelivery::where('email', $value)->first();
        if(empty($cli)) return true;
        else{
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Já existe um cadastro com este email.';
    }
}
