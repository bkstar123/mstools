<?php
/**
 * JsonToCSVConversionController class
 *
 * @author tuanha
 * @date 13-May-2022
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ConvertHttpLogJsonToCSV;
use Illuminate\Support\Facades\Storage;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class JsonToCSVConversionController extends Controller
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
            'directory' => 'uploaded-'.config('mstools.report.directory'),
            'maxFileSize' => config('mstools.maxFileUpload'),
            'disk' => config('mstools.report.disk')
        ]);
        if (!$uploadedFileData) {
            return response()->json(['error' => $fileupload->uploadError], 422);
        }
        ConvertHttpLogJsonToCSV::dispatch($uploadedFileData, auth()->user());
        return response()->json(['success' => "The JSON log file has been sent to MSTools for conversion"], 200);
    }
}
