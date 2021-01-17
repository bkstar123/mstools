<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UploadCustomCertificateToCloudflareResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var base64-encoded binary data
     */
    protected $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachment, $zones)
    {
        $this->attachment = $attachment;
        $this->zones = $zones;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ssl.updatecertcfzone')
                    ->subject('Cloudflare zone - update custom SSL configuration')
                    ->attachData(base64_decode($this->attachment), 'cf_zone_custom_ssl_update_report.csv');
    }
}