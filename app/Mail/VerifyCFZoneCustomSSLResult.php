<?php

namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyCFZoneCustomSSLResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var \App\Report
     */
    protected $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report, $zones)
    {
        $this->report = $report;
        $this->zones = $zones;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::disk($this->report->disk)->exists($this->report->path)) {
            return  $this->view('emails.ssl.cfzonessl')
                         ->subject('Cloudflare zone - custom SSL configuration')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'cf_zone_custom_ssl_check_result.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
