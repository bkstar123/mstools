<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
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
        $data = [];
        $pingdomCheck = resolve('pingdomCheck');
        $checks = $pingdomCheck->getChecks();
        if (!empty($checks)) {
            foreach ($checks as $key => $check) {
                array_push($data, [
                    'Check ID' => $check['id'],
                    'Created (UTC)' => Carbon::createFromTimestamp($check['created'])->setTimezone('UTC')->toDateTimeString(),
                    'Name' => $check['name'],
                    'Hostname' => trim($check['hostname']),
                    'Tags' => array_key_exists('tags', $check) ? json_encode(array_column($check['tags'], 'name')) : '',
                    'Type' => $check['type'],
                    'Verify Certificate' => $check['verify_certificate'],
                    'Status' => $check['status'],
                    'Last Check Time (UTC)' => array_key_exists('lasttesttime', $check) ? Carbon::createFromTimestamp($check['lasttesttime'])->setTimezone('UTC')->toDateTimeString() : '',
                ]);
            }
        } else {
            array_push($data, [
                'Check ID' => NUL,
                'Created (UTC)' => null,
                'Name' => null,
                'Hostname' => null,
                'Tags' => null,
                'Type' => $check['type'],
                'Verify Certificate' => null,
                'Status' => null,
                'Last Check Time (UTC)' => null,
            ]);
        }
        $headings = [
            'Check ID',
            'Created (UTC)',
            'Name',
            'Hostname',
            'Tags',
            'Type',
            'Verify Certificate',
            'Status',
            'Last Check Time (UTC)'
        ];
        ExportPingdomChecksCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->user);
    }
}
