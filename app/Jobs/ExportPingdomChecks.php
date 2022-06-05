<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\ExportPingdomChecksCompleted;

class ExportPingdomChecks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $outputFilename = md5(uniqid(rand(), true)."_".getmypid()."_".gethostname()."_".time()).'.csv';
        $subDirectory = md5(uniqid(rand(), true)."_".getmypid()."_".gethostname()."_".time());
        $outputFileLocation = [
            'disk' => config('mstools.pingdomreport.disk'),
            'path' => config('mstools.pingdomreport.directory').DIRECTORY_SEPARATOR.$subDirectory.DIRECTORY_SEPARATOR.$outputFilename
        ];
        Storage::disk($outputFileLocation['disk'])->makeDirectory(config('mstools.pingdomreport.directory').DIRECTORY_SEPARATOR.$subDirectory);
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
        ExportPingdomChecksCompleted::dispatch($outputFileLocation, $this->user);
    }
}
