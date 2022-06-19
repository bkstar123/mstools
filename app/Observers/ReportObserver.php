<?php
/**
 * ReportObserver
 *
 * @author: tuanha
 * @date: 19-June-2022
 */
namespace App\Observers;

use App\Report;
use Illuminate\Support\Facades\Storage;

class ReportObserver
{
    /**
     * Listen to the Report model deleted event.
     *
     * @param  \App\Report $report
     * @return void
     */
    public function deleted(Report $report)
    {
        Storage::disk($report->disk)->delete($report->path);
        if (empty(Storage::disk($report->disk)->allFiles(dirname($report->path)))) {
        	Storage::disk($report->disk)->deleteDirectory(dirname($report->path));
        }
    }
}
