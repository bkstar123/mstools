<?php
/**
 * ReportController
 * @author: tuanha
 * @date 19-June-2022
 */
namespace App\Http\Controllers;

use Exception;
use App\Report;
use Illuminate\Http\Request;
use Bkstar123\BksCMS\AdminPanel\Role;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * List all files belongs to current user or files belongs to all users if the current user has superadmin privilege
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (auth()->user()->hasRole(Role::SUPERADMINS)) {
                $reports = Report::simplePaginate(config('bkstar123_bkscms_adminpanel.pageSize'));
            } else {
                $reports = Report::where('admin_id', auth()->user()->id)
                                 ->orWhere('is_public', true)
                                 ->simplePaginate(config('bkstar123_bkscms_adminpanel.pageSize'));
            }
        } catch (Exception $e) {
            $reports = [];
        }
        return view('cms.reports', compact('reports'));
    }

    /**
     * Send file of given content type wuich is located at disk & path to browser
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendFileToBrowser(Request $request)
    {
        if (Storage::disk($request->query('disk'))->exists($request->query('path'))) {
            return Storage::disk($request->query('disk'))->download($request->query('path'), $request->query('name'), [
                'Content-Type' => $request->contentType
            ]);
        }
        flashing('There is no such file to download')->error()->flash();
        return back();
    }
}
