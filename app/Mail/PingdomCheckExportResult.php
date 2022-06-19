<?php

namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class PingdomCheckExportResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \App\Report
     */
    protected $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::disk($this->report->disk)->exists($this->report->path)) {
            return  $this->view('emails.pingdom.allchecks')
                         ->subject('Pingdom - List of checks')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'pingdom_checks.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
