<?php
/**
 * TrackingGoliveDxpSiteController Controller
 *
 * @author: tuanha
 * @date: 07-July-2022
 */
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\TrackingGoliveDxpSite;

class TrackingGoliveDxpSiteController extends Controller
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
            $trackings = TrackingGoliveDxpSite::search($searchText)
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
        	TrackingGoliveDxpSite::create([
        		'sites'    => $request->sites,
        		'admin_id' => $request->user()->id
        	]);
        	flashing("A go-live tracking has been created")
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
     * Enabling the selected tracking
     *
     * @param \App\TrackingGoliveDxpSite $tracking
     * @return \Illuminate\Http\Response
     */
    public function trackingOn(TrackingGoliveDxpSite $tracking)
    {
    	$tracking->status = TrackingGoliveDxpSite::ON;
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
     * @param \App\TrackingGoliveDxpSite $tracking
     * @return \Illuminate\Http\Response
     */
    public function trackingOff(TrackingGoliveDxpSite $tracking)
    {
    	$tracking->status = TrackingGoliveDxpSite::OFF;
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
     * @param \App\TrackingGoliveDxpSite $tracking
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrackingGoliveDxpSite $tracking)
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
            TrackingGoliveDxpSite::destroy($Ids);
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
