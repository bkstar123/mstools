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
        $outputFilename = md5(uniqid(rand(), true)) . '.csv';
        $outputTempLocation = [
            'disk' => $this->uploadedFileData['disk'],
            'path' => dirname($this->uploadedFileData['path']) . '/' . $outputFilename
        ];
        $fip = fopen(Storage::disk($this->uploadedFileData['disk'])->path($this->uploadedFileData['path']), 'r');
        $fop = fopen(Storage::disk($outputTempLocation['disk'])->path($outputTempLocation['path']), 'w');
        fputcsv($fop, [
            'Category',
            'Time',
            'ResourceId',
            'EventStampType',
            'EventPrimaryStampName',
            'EventStampName',
            'Host',
            'EventIpAddress',
            'UserAgent',
            'Cookie',
            'ScStatus',
            'CsUsername',
            'Result',
            'CsHost',
            'CsMethod',
            'CsBytes',
            'CIp',
            'SPort',
            'Referer',
            'CsUriStem',
            'TimeTaken',
            'ScBytes',
            'ComputerName'
        ]);
        if ($fip) {
            while (!feof($fip)) {
                $line = fgets($fip);
                if ($line) {
                    $lineArray = json_decode($line, true);
                    $properties = json_decode($lineArray['properties'], true);
                    fputcsv($fop, [
                        $lineArray['category'],
                        $lineArray['time'],
                        $lineArray['resourceId'],
                        $lineArray['EventStampType'],
                        $lineArray['EventPrimaryStampName'],
                        $lineArray['EventStampName'],
                        $lineArray['Host'],
                        $lineArray['EventIpAddress'],
                        $properties['UserAgent'],
                        $properties['Cookie'],
                        $properties['ScStatus'],
                        $properties['CsUsername'],
                        $properties['Result'],
                        $properties['CsHost'],
                        $properties['CsMethod'],
                        $properties['CsBytes'],
                        $properties['CIp'],
                        $properties['SPort'],
                        $properties['Referer'],
                        $properties['CsUriStem'],
                        $properties['TimeTaken'],
                        $properties['ScBytes'],
                        $properties['ComputerName']
                    ]);
                }
            }
        }
        fclose($fip);
        fclose($fop);
        HttpLogJson2CsvConversionDone::dispatch($outputTempLocation, $this->user);
        app(FileUpload::class)->delete($this->uploadedFileData['disk'], $this->uploadedFileData['path']);
    }
}
