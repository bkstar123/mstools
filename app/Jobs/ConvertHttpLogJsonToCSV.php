<?php
/**
 * ConvertHttpLogJsonToCSV Job
 *
 * @author: tuanha
 * @last-mod: 13-May-2022
 */
namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Illuminate\Bus\Queueable;
use Bkstar123\BksCMS\AdminPanel\Admin;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\HttpLogJson2CsvConversionDone;
use App\Http\Components\GenerateCustomUniqueString;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class ConvertHttpLogJsonToCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    public $uploadedFileData;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * The number of seconds the job can run before timing out
     * must be on several seconds less than the queue connection's retry_after defined in the config/queue.php
     *
     * @var int
     */
    public $timeout = 1190;

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
        $outputFileLocation = [
            'disk' => config('mstools.report.disk'),
            'path' => config('mstools.report.directory').DIRECTORY_SEPARATOR.$this->generateUniqueString().DIRECTORY_SEPARATOR.$this->generateUniqueString('.csv')
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(dirname($outputFileLocation['path']));
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
        $report = Report::create([
            'name'     => 'DotNet Core Log ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        HttpLogJson2CsvConversionDone::dispatch($this->user);
        app(FileUpload::class)->delete($this->uploadedFileData['disk'], $this->uploadedFileData['path']);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        app(FileUpload::class)->delete($this->uploadedFileData['disk'], $this->uploadedFileData['path']);
        JobFailing::dispatch($this->user);
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
