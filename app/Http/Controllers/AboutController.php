<?php
/**
 * AboutController
 *
 * @author: tuanha
 * @date: 17-July-2022
 */
namespace App\Http\Controllers;

use App\About;
use Exception;
use Illuminate\Http\Request;

class AboutController extends Controller
{
	/**
	 * Show the about page
	 *
	 * @return Illuminate\Http\Response
	 */
    public function show()
    {
    	$about = About::where('name', 'about-me')->first();
    	return view('cms.about.index', compact('about'));
    }

	/**
	 * Show the edit/create view for about page
	 *
	 * @return Illuminate\Http\Response
	 */
    public function edit()
    {
    	$about = About::where('name', 'about-me')->first();
    	return view('cms.about.edit', compact('about'));
    }

	/**
	 * Create/update the about page
	 *
	 * @param Illuminate\Http\Request $request
	 * @return Illuminate\Http\Response
	 */
    public function store(Request $request)
    {
    	try {
    		About::updateOrCreate(['name' => 'about-me'], ['content' => $request->content]);
    		flashing('The operation has been successfully completed')->success()->flash();
    	} catch (Exception $e) {
    		flashing('Oops, there is something wrong. The operation has been aborted')->error()->flash();
    	}
    	return back();
    }
}
