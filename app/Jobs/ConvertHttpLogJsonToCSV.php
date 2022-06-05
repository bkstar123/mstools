<?php
/**
 * ConvertHttpLogJsonToCSV Job
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Bkstar123\BksCMS\AdminPanel\Admin;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\HttpLogJson2CsvConversionDone;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class ConvertHttpLogJsonToCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $uploadedFileData;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $uploadedFileData, Admin $user)
    {
        $this->uploadedFileData = $uploadedFileData;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $outputFilename = md5(uniqid(rand(), true)."_".getmypid()."_".gethostname()."_".time()).'.csv';
        $outputFileLocation = [
            'disk' => $this->uploadedFileData['disk'],
            'path' => dirname($this->uploadedFileData['path']).DIRECTORY_SEPARATOR.$outputFilename
        ];
        $fip = fopen(Storage::disk($this->uploadedFileData['disk'])->path($this->uploadedFileData['path']), 'r');
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        if ($fip) {
            // Read the first time to scan the JSON file for all keys to build the list of headers for the output CSV file
            $headers = [];
            while (!feof($fip)) {
                $line = fgets($fip);
                if ($line) {
                    $headers = array_merge($headers, array_diff(array_keys($this->jsonToArray($line)), $headers));
                }
            }
            fputcsv($fop, $headers);

            // Read the second time to write data to the output CSV file
            rewind($fip);
            while (!feof($fip)) {
                $line = fgets($fip);
                if ($line) {
                    $lineItems = $this->jsonToArray($line);
                    $data = [];
                    foreach ($headers as $header) {
                        array_push($data, $lineItems[$header] ?? '-');
                    }
                    fputcsv($fop, array_values($data));
                }
            }
        }
        fclose($fip);
        fclose($fop);
        HttpLogJson2CsvConversionDone::dispatch($outputFileLocation, $this->user);
        app(FileUpload::class)->delete($this->uploadedFileData['disk'], $this->uploadedFileData['path']);
    }

    /**
     * Check if the given argument is an JSON string
     *
     * @param string
     * @return bool
     */
    protected function isJSONString(string $str)
    {
        return is_array(json_decode($str, true));
    }

    /**
     * Convert the given JSON string into a flat array 
     *
     * @param string
     * @return array
     */
    protected function jsonToArray(string $str)
    {
        $data = [];
        if ($this->isJSONString($str)) {
            $items = json_decode($str, true);
            foreach ($items as $key => $value) {
                if (!$this->isJSONString($value)) {
                    $data[$key] = $value;
                } else {
                    $data = array_merge($data, $this->jsonToArray($value));
                }
            }
        }
        return $data;
    }
}
