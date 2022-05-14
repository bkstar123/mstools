<?php
/**
 * MiscellaneousController class
 *
 * @author tuanha
 * @date 13-May-2022
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ConvertHttpLogJsonToCSV;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class MiscellaneousController extends Controller
{
    /**
     * Handle the uploaded HTTP Log JSON file
     *
     * @param \Illuminate\Http\Request $request
     * @param \Bkstar123\LaravelUploader\Contracts\FileUpload $fileupload
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleUploadedHttpLogJsonFile(Request $request, FileUpload $fileupload)
    {
        $uploadedFileData = $fileupload->handle($request, 'httplog', [
            'allowedExtensions' => ['json'],
            'directory' => 'dotnetcore-httplog',
            'maxFileSize' => config('mstools.maxFileUpload'),
            'disk' => 'local'
        ]);
        if (!$uploadedFileData) {
            return response()->json(['error' => $fileupload->uploadError], 422);
        }
        ConvertHttpLogJsonToCSV::dispatch($uploadedFileData, auth()->user());
        return response()->json(['success' => "MSTool is proceeding the conversion of your uploaded JSON file and will email the result to you"], 200);
    }
}
