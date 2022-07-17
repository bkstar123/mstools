<?php

namespace App\Jobs;

use Exception;
use App\Report;
use Carbon\Carbon;
use App\Events\JobFailing;
use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\GetPingdomChecksAvgSummaryCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class GetPingdomChecksAvgSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    protected $checkIDs;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

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
    public function __construct($checkIDs, $from, $to, $user)
    {
        $this->checkIDs = $checkIDs;
        $this->from = $from;
        $this->to = $to;
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
            'From (UTC)',
            'To (UTC)',
            'Check ID',
            'Total Downtime',
            'Total Uptime',
            'Total Unknown',
            '% Uptime',
            '% Unknown'
        ]);
        $pingdomCheck = resolve('pingdomCheck');
        foreach ($this->checkIDs as $id) {
            $fromTS = Carbon::parse($this->from, 'UTC')->timestamp;
            $toTS = Carbon::parse($this->to, 'UTC')->timestamp;
            $report = $pingdomCheck->getCheckSummaryAverage($id, $fromTS, $toTS);
            if ($report) {
                fputcsv($fop, [
                    $this->from,
                    $this->to,
                    $id,
                    CarbonInterval::seconds($report['status']['totaldown'])->cascade()->forHumans(),
                    CarbonInterval::seconds($report['status']['totalup'])->cascade()->forHumans(),
                    CarbonInterval::seconds($report['status']['totalunknown'])->cascade()->forHumans(),
                    round($report['status']['totalup'] * 100 / ($toTS - $fromTS), 2),
                    round($report['status']['totalunknown'] * 100 / ($toTS - $fromTS), 2),
                ]);
            } else {
                fputcsv($fop, [$this->from, $this->to, $id, '', '', '', '', '']);
            }
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Average uptime summary of the given list of pingdom checks ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        GetPingdomChecksAvgSummaryCompleted::dispatch($this->user);
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
