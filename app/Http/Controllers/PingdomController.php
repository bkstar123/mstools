<?php
/**
 * PingdomController
 *
 * @author: tuanha
 * @date: 23-Oct-2021
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ExportPingdomChecks;
use App\Jobs\GetPingdomChecksDetails;
use App\Jobs\GetPingdomChecksAvgSummary;
use App\Http\Components\RequestByUserThrottling;

class PingdomController extends Controller
{
    use RequestByUserThrottling;

    /**
     * Export all Pingdom Checks
     *
     * @return Illuminate\Http\Response
     */
    public function exportChecks()
    {
        if (!$this->isThrottled()) {
            $this->setRequestThrottling(20);
            ExportPingdomChecks::dispatch(auth()->user());
            flashing('MSTool is exporting Pingdom checks and will mail the result to you')
            ->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 20 seconds before sending more')
            ->warning()
            ->flash();
        }
        return back();
    }

    /**
     * Get details of given Pingdom checks
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function getChecks(Request $request)
    {
        $request->validate([
            'checks' => 'required'
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $checkIDs = array_map(function ($checkID) {
                return trim($checkID);
            }, explode(',', $request->checks));
            GetPingdomChecksDetails::dispatch($checkIDs, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }

    /**
     * Get average uptime summary of Pingdom checks
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function getAverageSummary(Request $request)
    {
        $request->validate([
            'avgsmChecks' => 'required',
            'avgsmFrom'   => 'required|date',
            'avgsmTo'     => 'required|date|after:avgsmFrom'
        ], [
            'avgsmTo.after' => "The To (UTC) field must be a date after the one in the From (UTC) field"
        ]);
        if (!$this->isThrottled()) {
            $this->setRequestThrottling();
            $checkIDs = array_map(function ($checkID) {
                return trim($checkID);
            }, explode(',', $request->avgsmChecks));
            GetPingdomChecksAvgSummary::dispatch($checkIDs, $request->avgsmFrom, $request->avgsmTo, auth()->user());
            flashing('MSTool is processing the request')->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 10 seconds before sending another one')->warning()->flash();
        }
        return back();
    }
}
