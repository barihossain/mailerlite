<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use MailerLiteApi\MailerLite;

class CheckApiKeyRule implements Rule
{
    /**
     * Error message for invalid API key
     *
     * @var string
     */
    protected $errorMessage;

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
        $subscribersApi = (new MailerLite($value))->subscribers();
        $subscribers = $subscribersApi->get();

        //a valid API key with no subscribers gives null
        if (!is_null($subscribers->first()) && property_exists($subscribers->first(), 'error')) {
            $this->errorMessage = "Error with your key. Code: " . $subscribers->first()->error->code . ", Message: " . $subscribers->first()->error->message;
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
