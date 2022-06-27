<?php

namespace App\Jobs;

use Exception;
use App\Events\JobFailing;
use App\Exports\ExcelExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use App\Events\UpdateCFFWRuleCompleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateCFFWRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var array
     */
    public $request;

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
    public function __construct(array $zones, $request, $user)
    {
        $this->zones = $zones;
        $this->request = $request;
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
        $filter_update_status = [
            'msg' => '. No change made for the rule filter expression',
            'status' => true
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
                'description' => $this->request['description']
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
            if (\Arr::has($this->request, 'new_action') && !empty($this->request['new_action'])) {
                $rule->action = $this->request['new_action'];
                if ($this->request['new_action'] == 'bypass') {
                    $rule->products = $this->request['products'];
                } else {
                    $rule->products = [];
                }
            }
            if (\Arr::has($this->request, 'new_expression') && !empty($this->request['new_expression'])) {
                $rule->filter->expression = $this->request['new_expression'];
                if (!$zoneFW->updateFWRuleFilterForZone($zoneID, $rule->filter)) {
                    $filter_update_status['msg'] = ". Failed to update the rule filter, please check if the same filter expression already exists for the zone";
                    $filter_update_status['status'] = false;
                } else {
                    $filter_update_status['msg'] = ". The rule filter was succeesfully updated";
                    $filter_update_status['status'] = true;
                }
            }
            if (\Arr::has($this->request, 'new_description') && !empty($this->request['new_description'])) {
                $rule->description = $this->request['new_description'];
            }
            if (\Arr::has($this->request, 'paused') && !empty($this->request['paused'])) {
                $rule->paused = ($this->request['paused'] == 'true') ? true : false;
            }
            if (!$zoneFW->updateFWRuleForZone($zoneID, $rule)) {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => $filter_update_status['status'] ? 'Partially Done' : 'No',
                    'Comment' => 'Failed to update the given firewall rule on this zone' . $filter_update_status['msg']
                ]);
                continue;
            } else {
                array_push($data, [
                    'Zone' => $zone,
                    'isCompleted' => $filter_update_status['status'] ? 'Yes' : 'Partially Done',
                    'Comment' => 'The given firewall rule has been successfully updated for this zone' . $filter_update_status['msg']
                ]);
            }
        }
        $headings = ['Zone', 'isCompleted', 'Comment'];
        UpdateCFFWRuleCompleted::dispatch(Excel::raw(new ExcelExport($data, $headings), 'Xlsx'), $this->zones, $this->user, $this->request['new_description'] ?? $this->request['description']);
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
