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

    /**
     * Send the Pingdom report file to browser
     *
     * @param \Illuminate\Http\Request $request
     */
    public function sendReportFileToBrowser(Request $request)
    {
        if (Storage::disk($request->query('disk'))->exists($request->query('filepath'))) {
            return Storage::disk($request->query('disk'))->download($request->query('filepath'), 'pingdom_checks.csv', ['Content-Type' => 'text/csv']);
        }
        flashing('There is no such file to download')->error()->flash();
        return redirect()->route('dashboard.index');
    }
}
