<?php

namespace App\Jobs;

use Exception;
use App\Events\JobFailing;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use App\Events\DeleteCFFWRuleCompleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteCFFWRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var string
     */
    public $ruleDescription;

    /**
     * @var Bkstar123\BksCMS\AdminPanel\Admin
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
    public function __construct(array $zones, $ruleDescription, $user)
    {
        $this->zones = $zones;
        $this->ruleDescription = $ruleDescription;
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
        $filter_delete_status = [
            'msg' => null,
            'status' => null
        ];
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
            $rules = $zoneFW->getFWRuleForZone($zoneID, [
                'description' => $this->ruleDescription
            ]);
            if (!$rules) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'Comment' => 'There is no firewall rule with the given description for this zone'
                ]);
                continue;
            }
            if (count($rules) > 1) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => 'No',
                    'Comment' => 'Found more than one rule with the given description. You must give a more specific description to only operate on your concerned rule'
                ]);
                continue;
            }
            $rule = \Arr::first($rules);
            if (!$zoneFW->deleteFWRuleFilterForZone($zoneID, $rule->filter)) {
                $filter_delete_status['msg'] = ". Failed to delete the rule filter, please manually verify & delete it using Cloudflare API";
                $filter_delete_status['status'] = false;
            } else {
                $filter_delete_status['msg'] = ". The rule filter was succeesfully deleted";
                $filter_delete_status['status'] = true;
            }
            if (!$zoneFW->deleteFWRuleForZone($zoneID, $rule)) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => $filter_delete_status['status'] ? 'Partially Done' : 'No',
                    'Comment' => 'Failed to delete the given firewall rule on this zone' . $filter_delete_status['msg']
                ]);
                continue;
            } else {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => $filter_delete_status['status'] ? 'Yes' : 'Partially Done',
                    'Comment' => 'The given firewall rule has been successfully deleted for this zone' . $filter_delete_status['msg']
                ]);
            }
        }
        $headings = ['Zone', 'isCompleted', 'Comment'];
        DeleteCFFWRuleCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user, $this->ruleDescription);
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
