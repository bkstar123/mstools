<?php
/**
 * HttpLogJson2CsvResult Mailable
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Mail;

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
     * @var array
     */
    protected $outputTempLocation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($outputTempLocation)
    {
        $this->outputTempLocation = $outputTempLocation;
    }

    /**
     * Destroy the message instance.
     *
     * @return void
     */
    public function __destruct()
    {
        app(FileUpload::class)->delete($this->outputTempLocation['disk'], $this->outputTempLocation['path']);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.miscellaneous.httplogjson2csv')
                    ->subject('Convert .NET Core HTTP Log From JSON To CSV')
                    ->attach(Storage::disk($this->outputTempLocation['disk'])->path($this->outputTempLocation['path']), [
                        'as' => 'http_log.csv',
                        'mime' => 'text/csv'
                    ]);
    }
}
