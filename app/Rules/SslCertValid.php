<?php
/**
* SslCertValid Rule
*
* @author: tuanha
* @last-mod: 16-Jan-2021
*/
namespace App\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Spatie\SslCertificate\SslCertificate;

class SslCertValid implements Rule
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
        try {
            return openssl_x509_read($value) && SslCertificate::createFromString($value)->isValid();
        } catch (Exception $e) {
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
        return 'The certificate is invalid';
    }
}
