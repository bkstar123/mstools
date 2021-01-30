<?php
/**
* SslKeyMatch Rule
*
* @author: tuanha
* @last-mod: 16-Jan-2021
*/
namespace App\Rules;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class SslKeyMatch implements Rule
{
    /**
     * @var string
     */
    protected $certificate;

    /**
     * Create a new rule instance.
     *
     * @param string $certificate
     * @return void
     */
    public function __construct($certificate)
    {
        $this->certificate = $certificate;
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
            $pubKey = openssl_pkey_get_public($this->certificate);
            $originPayload = Str::random(40);
            openssl_public_encrypt($originPayload, $cryptedPayload, $pubKey);
            openssl_private_decrypt($cryptedPayload, $decryptedPayload, $value);
            return openssl_x509_check_private_key($this->certificate, $value) && $originPayload === $decryptedPayload;
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
        return 'The :attribute must be matched with the given certificate';
    }
}
