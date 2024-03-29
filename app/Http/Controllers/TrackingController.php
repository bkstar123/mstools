<?php
/**
 * TrackingController Controller
 *
 * @author: tuanha
 * @date: 07-July-2022
 */
namespace App\Http\Controllers;

use Exception;
use App\Tracking;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $searchText = request()->input('search');
        try {
            $trackings = Tracking::search($searchText)
                    ->simplePaginate(config('bkstar123_bkscms_adminpanel.pageSize'))
                    ->appends([
                        'search' => $searchText
                    ]);
        } catch (Exception $e) {
            $trackings = [];
        }
        return view('cms.trackings.index', compact('trackings'));
    }
    
    /**
     * Show create form
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cms.trackings.create');
    }

    /**
     * Store a resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'sites' => 'required'
        ]);
        try {
            Tracking::create([
                'sites'         => $request->sites,
                'admin_id'      => $request->user()->id,
                'tracking_size' => count(explode(',', $request->sites))
            ]);
            flashing("A go-live tracking has been created")
                ->success()
                ->flash();
        } catch (Exception $e) {
            dd($e);
            flashing("The submitted action failed to be executed due to some unknown error")
                ->error()
                ->flash();
        }
        return back();
    }

    /**
     * Show a resource
     *
     * @param \App\Tracking $tracking
     * @return \Illuminate\Http\Response
     */
    public function show(Tracking $tracking)
    {
        return view('cms.trackings.show', compact('tracking'));
    }

    /**
     * Show a resource
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Tracking $tracking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tracking $tracking)
    {
        $tracking->sites = $request->sites;
        $tracking->tracking_size = !empty($request->sites) ? count(explode(',', $request->sites)) : 0;
        try {
            $tracking->save();
            flashing('The tracking has been successfully updated')
                ->success()
                ->flash();
        } catch (Exception $e) {
            flashing('The submitted action failed to be executed due to some unknown error')
                ->error()
                ->flash();
        }
        return back();
    }

    /**
     * Enabling the selected tracking
     *
     * @param \App\Tracking $tracking
     * @return \Illuminate\Http\Response
     */
    public function trackingOn(Tracking $tracking)
    {
        $tracking->status = Tracking::ON;
        try {
            $tracking->save();
            flashing("The selected tracking has been successfully disabled")
                ->success()
                ->flash();
        } catch (Exception $e) {
            flashing("The submitted action failed to be executed due to some unknown error")
                ->error()
                ->flash();
        }
        return back();
    }

    /**
     * Disabling the selected tracking
     *
     * @param \App\Tracking $tracking
     * @return \Illuminate\Http\Response
     */
    public function trackingOff(Tracking $tracking)
    {
        $tracking->status = Tracking::OFF;
        try {
            $tracking->save();
            flashing("The selected tracking has been successfully enabled")
                ->success()
                ->flash();
        } catch (Exception $e) {
            flashing("The submitted action failed to be executed due to some unknown error")
                ->error()
                ->flash();
        }
        return back();
    }

    /**
     * Destroy the selected tracking
     *
     * @param \App\Tracking $tracking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tracking $tracking)
    {
        try {
            $tracking->delete();
            flashing("The selected tracking has been successfully removed")
                ->success()
                ->flash();
        } catch (Exception $e) {
            flashing("The submitted action failed to be executed due to some unknown error")
                ->error()
                ->flash();
        }
        return back();
    }

    /**
     * Destroy multiple selected trackings
     *
     * @return \Illuminate\Http\Response
     */
    public function massiveDestroy()
    {
        $Ids = explode(',', request()->input('Ids'));
        try {
            Tracking::destroy($Ids);
            flashing('All selected trackings have been removed')
                ->success()
                ->flash();
        } catch (Exception $e) {
            flashing("The submitted action failed to be executed due to some unknown error")
                ->error()
                ->flash();
        }
        return back();
    }
}
