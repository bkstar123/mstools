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
    public $changedIPs;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($changedIPs)
    {
        $this->changedIPs = $changedIPs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.cfjdcloudipchanged')
                    ->subject('Cloudflare IP change detected);
    }
}
