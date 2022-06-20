<?php
/**
 * HttpLogJson2CsvResult Mailable
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Mail;

use App\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class HttpLogJson2CsvResult extends Mailable
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
            return  $this->view('emails.miscellaneous.httplogjson2csv')
                         ->subject('Convert .NET Core HTTP Log From JSON To CSV')
                         ->attach(Storage::disk($this->report->disk)->path($this->report->path), [
                            'as' => 'DotNet Core Log.csv',
                            'mime' => 'text/csv'
                        ]);
        }
    }
}
