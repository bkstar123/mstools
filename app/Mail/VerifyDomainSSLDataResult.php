<?php

namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyDomainSSLDataResult extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $domains;

    /**
     * @var \App\Report
     */
    protected $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report, $domains)
    {
        $this->report = $report;
        $this->domains = $domains;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::disk($this->report->disk)->exists($this->report->path)) {
            return  $this->view('emails.ssl.domainssldata')
                         ->subject('Domain SSL Data')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'domain_ssl_data_check_result.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
