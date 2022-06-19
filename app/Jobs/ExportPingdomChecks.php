<?php

namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\ExportPingdomChecksCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class ExportPingdomChecks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    protected $user;

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
    public function __construct($user)
    {
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
        $fop = fopen(Storage::disk($outputFileLocation['disk'])->path($outputFileLocation['path']), 'w');
        fputcsv($fop, [
            'Check ID',
            'Created (UTC)',
            'Name',
            'Hostname',
            'Tags',
            'Type',
            'Verify Certificate',
            'Status',
            'Last Check Time (UTC)'
        ]);
        $pingdomCheck = resolve('pingdomCheck');
        $checks = $pingdomCheck->getChecks();
        if (!empty($checks)) {
            foreach ($checks as $key => $check) {
                fputcsv($fop, [
                    $check['id'],
                    Carbon::createFromTimestamp($check['created'])->setTimezone('UTC')->toDateTimeString(),
                    $check['name'],
                    trim($check['hostname']),
                    array_key_exists('tags', $check) ? json_encode(array_column($check['tags'], 'name')) : '',
                    $check['type'],
                    $check['verify_certificate'],
                    $check['status'],
                    array_key_exists('lasttesttime', $check) ? Carbon::createFromTimestamp($check['lasttesttime'])->setTimezone('UTC')->toDateTimeString() : '',
                ]);
            }
        } else {
            fputcsv($fop, [null, null, null, null, null, null, null, null, null]);
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'List of pingdom checks ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        ExportPingdomChecksCompleted::dispatch($report, $this->user);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        JobFailing::dispatch($this->user);
    }
}
