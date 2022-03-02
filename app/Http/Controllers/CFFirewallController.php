<?php
/**
 * CFFirewallController
 *
 * @author: tuanha
 * @date: 28-Feb-2022
 */
namespace App\Http\Controllers;

use App\Jobs\CreateCFFWRule;
use Illuminate\Http\Request;
use App\Http\Components\RequestByUserThrottling;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRule;
use Bkstar123\CFBuddy\Components\CFFWRule\CFFWRuleFilter;

class CFFirewallController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Create firewall rule for Cloudflare zones
     */
    public function createFWRule(Request $request)
    {
        // Validate input & throttle request & authorization
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $zones = explode(",", $request->zones);
            $filter = new CFFWRuleFilter($request->expression);
            $rule = new CFFWRule($request->description, false, $filter, $request->action, $request->products ?? []);
            CreateCFFWRule::dispatch($zones, $rule, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->flash();
        }
        return back();
    }
}
