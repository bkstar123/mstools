<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class PingdomCheckExportResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $outputFileLocation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($outputFileLocation)
    {
        $this->outputFileLocation = $outputFileLocation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::disk($this->outputFileLocation['disk'])->exists($this->outputFileLocation['path'])) {
            return  $this->view('emails.pingdom.allchecks')
                         ->subject('Pingdom - All checks')
                         ->attach(Storage::disk($this->outputFileLocation['disk'])->path($this->outputFileLocation['path']), [
                            'as' => 'pingdom_checks.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
