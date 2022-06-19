<?php

namespace App\Jobs;

use Exception;
use App\Events\JobFailing;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Bkstar123\BksCMS\AdminPanel\Admin;
use Illuminate\Queue\SerializesModels;
use App\Events\CreateCFFWRuleCompleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule;

class CreateCFFWRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var \Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule
     */
    public $rule;

    /**
     * @var \Bkstar123\BksCMS\AdminPanel\Admin
     */
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $zones, CFFWRule $rule, Admin $user)
    {
        $this->zones = $zones;
        $this->rule = $rule;
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
        $zoneMgmt = resolve('zoneMgmt');
        $zoneFW = resolve('cfZoneFW');
        foreach ($this->zones as $zone) {
            $zone = idn_to_ascii(strtolower(trim($zone)), IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $zoneID = $zoneMgmt->getZoneID($zone);
            if (empty($zoneID)) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'Comment' => "Failed to check this zone's data on Cloudflare"
                ]);
                continue;
            }
            if (!$zoneFW->createFirewallRule($zoneID, $this->rule)) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'Comment' => 'Failed to create the given firewall rule on this zone'
                ]);
                continue;
            } else {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'Yes',
                    'Comment' => 'The given firewall rule has been successfully created for this zone'
                ]);
            }
        }
        $headings = ['Zone', 'isCompleted', 'Comment'];
        CreateCFFWRuleCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user, $this->rule->description);
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
