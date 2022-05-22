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
            return  $this->view('emails.miscellaneous.httplogjson2csv')
                         ->subject('Convert .NET Core HTTP Log From JSON To CSV')
                         ->attach(Storage::disk($this->outputFileLocation['disk'])->path($this->outputFileLocation['path']), [
                            'as' => 'log.csv',
                            'mime' => 'text/csv'
                        ]);
        }     
    }
}
