<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\ClienteEcommerce;

class EmailDupEcommerce implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $config = null;
    public function __construct($config)
    {
        $this->config = $config;
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
        
        $cli = ClienteEcommerce::where('email', $value)->where('empresa_id', $this->config->empresa_id)->first();
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
