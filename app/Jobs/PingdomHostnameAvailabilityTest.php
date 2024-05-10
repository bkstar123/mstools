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
use App\Http\Components\GenerateCustomUniqueString;
use App\Events\PingdomTestHostnameAvailabilityCompleted;

class PingdomHostnameAvailabilityTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    protected $payload;

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
    public function __construct($payload, $user)
    {
        $this->payload = $payload;
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
            'Hostname',
            'ResponseTime (ms)',
            'Status',
            'ProbeLocation',
            'StatusDescription',
            'StatusLongDescription',
        ]);
        $pingdomTest = resolve('pingdomTest');
        $hostnames = array_map(function ($hostname) {
            return idn_to_ascii(strtolower(trim($hostname)), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        }, explode(",", $this->payload['hostnames']));
        foreach ($hostnames as $hostname) {
            $res = $pingdomTest->testAvailability(
                $hostname,
                $this->payload['shouldcontain'],
                $this->payload['shouldnotcontain'],
                $this->payload['ssldowndaysbefore'] ?? 1,
                $this->payload['targetpath'] ?? "/",
                $this->payload['prober']
            );
            if (empty($res)) {
                fputcsv($fop, [$hostname, '', '', '', '', '']);
                continue;
            }
            fputcsv($fop, [
                $hostname,
                $res['responsetime'] ?? '',
                $res['status'] ?? '',
                $res['probedesc'] ?? '',
                $res['statusdesc'] ?? '',
                $res['statusdesclong'] ?? ''
            ]);
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Result of Pingdom test availability for hostnames ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        PingdomTestHostnameAvailabilityCompleted::dispatch($this->user);
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
