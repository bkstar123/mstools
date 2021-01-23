<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyDomainSSLDataResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $domains;

    /**
     * @var base64-encoded binary data
     */
    protected $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachment, $domains)
    {
        $this->attachment = $attachment;
        $this->domains = $domains;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ssl.domainssldata')
                    ->subject('Domain SSL Data')
                    ->attachData(base64_decode($this->attachment), 'domain_ssl_data_check_result.xlsx');
    }
}
