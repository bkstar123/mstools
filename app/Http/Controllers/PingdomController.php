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
use Illuminate\Support\Facades\Storage;
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
            ->success()
            ->flash();
        } else {
            flashing('MSTool is busy processing your first request, please wait for 20 seconds before sending more')
            ->warning()
            ->flash();
        }
        return back();
    }
}
