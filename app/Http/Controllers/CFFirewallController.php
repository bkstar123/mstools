<?php
/**
 * CFFirewallController
 *
 * @author: tuanha
 * @date: 28-Feb-2022
 */
namespace App\Http\Controllers;

use App\Jobs\CreateCFFWRule;
use App\Jobs\DeleteCFFWRule;
use App\Jobs\UpdateCFFWRule;
use App\Http\Requests\CFFWRuleRequest;
use App\Http\Requests\DeleteCFFWRuleRequest;
use App\Http\Requests\UpdateCFFWRuleRequest;
use App\Http\Components\RequestByUserThrottling;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter;

class CFFirewallController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Create firewall rule for Cloudflare zones
     *
     * @param \App\Http\Requests\CFFWRuleRequest
     */
    public function createFWRule(CFFWRuleRequest $request)
    {
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = explode(",", $request->zones);
            $filter = new CFFWRuleFilter($request->expression);
            $rule = new CFFWRule($request->description, false, $filter, $request->action, $request->products ?? []);
            CreateCFFWRule::dispatch($zones, $rule, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }

    /**
     * Update a firewall rule for Cloudflare zones
     *
     * @param \App\Http\Requests\UpdateCFFWRuleRequest
     */
    public function updateFWRule(UpdateCFFWRuleRequest $request)
    {
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = explode(",", $request->zones);
            UpdateCFFWRule::dispatch($zones, $request->all(), $request->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }

    /**
     * Delete a firewall rule for Cloudflare zones
     *
     * @param \App\Http\Requests\DeleteCFFWRuleRequest
     */
    public function deleteFWRule(DeleteCFFWRuleRequest $request)
    {
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = explode(",", $request->zones);
            DeleteCFFWRule::dispatch($zones, $request->description, $request->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }
}
