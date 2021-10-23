<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PingdomCheckExportResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var base64-encoded binary data
     */
    protected $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.pingdom.allchecks')
                    ->subject('Pingdom - All checks')
                    ->attachData(base64_decode($this->attachment), 'pingdom_checks.xlsx');
    }
}
