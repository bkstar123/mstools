<?php
/**
 * GetPingdomChecksDetails
 *
 * @author: tuanha
 * @date: 16-July-2022
 */
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
use App\Events\GetPingdomChecksDetailsCompleted;
use App\Http\Components\GenerateCustomUniqueString;

class GetPingdomChecksDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateCustomUniqueString;

    /**
     * @var array
     */
    protected $checkIDs;

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
    public function __construct($checkIDs, $user)
    {
        $this->checkIDs = $checkIDs;
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
            'Status',
            'Last Check Time (UTC)',
            'Last Down Start (UTC)',
            'Last Down End (UTC)',
        ]);
        $pingdomCheck = resolve('pingdomCheck');
        foreach ($this->checkIDs as $id) {
            $check = $pingdomCheck->getCheck($id);
            if (empty($check)) {
                fputcsv($fop, [$id, '', '', '', '', '', '', '', '', '']);
                continue;
            }
            fputcsv($fop, [
                $id,
                Carbon::createFromTimestamp($check['created'])->setTimezone('UTC')->toDateTimeString(),
                $check['name'],
                trim($check['hostname']),
                array_key_exists('tags', $check) ? json_encode(array_column($check['tags'], 'name')) : '',
                array_key_exists('type', $check) ? json_encode($check['type']) : '',
                $check['status'],
                array_key_exists('lasttesttime', $check) ? Carbon::createFromTimestamp($check['lasttesttime'])->setTimezone('UTC')->toDateTimeString() : '',
                array_key_exists('lastdownstart', $check) ? Carbon::createFromTimestamp($check['lastdownstart'])->setTimezone('UTC')->toDateTimeString() : '',
                array_key_exists('lastdownend', $check) ? Carbon::createFromTimestamp($check['lastdownend'])->setTimezone('UTC')->toDateTimeString() : ''
            ]);
        }
        fclose($fop);
        $report = Report::create([
            'name'     => 'Details of the given list of pingdom checks ' . Carbon::createFromTimestamp(time())->setTimezone('UTC')->toDateTimeString()."(UTC).csv",
            'admin_id' => $this->user->id,
            'disk'     => $outputFileLocation['disk'],
            'path'     => $outputFileLocation['path'],
            'mime'     => 'text/csv'
        ]);
        GetPingdomChecksDetailsCompleted::dispatch($this->user);
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
