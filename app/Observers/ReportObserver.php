<?php
/**
 * ReportObserver
 *
 * @author: tuanha
 * @date: 19-June-2022
 */
namespace App\Observers;

use App\Report;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

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
        app(FileUpload::class)->delete($report->disk, $report->path);
    }
}
