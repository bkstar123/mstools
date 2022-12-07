<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CFJDCloudIPChangeNotify extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $addedIPs;

    /**
     * @var string
     */
    public $removedIPs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($addedIPs, $removedIPs)
    {
        $this->addedIPs = $addedIPs;
        $this->removedIPs = $removedIPs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.cfjdcloudipchanged')
                    ->subject('Cloudflare IP change detected');
    }
}
