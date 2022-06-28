<?php

namespace App\Console\Commands;

use App\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class PurgeReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge reports';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get short-lived reports & aged more than <short_ttl> minutes or long-lived reports & aged more than <long_ttl> minutes
        $expiredReports = Report::where(function ($query) {
                                      $query->where('is_longlive', false)
                                            ->where('created_at', '<', Carbon::now()->subMinutes(config('mstools.report.short_ttl')));
                                 })->orWhere(function ($query) {
                                      $query->where('is_longlive', true)
                                            ->where('created_at', '<', Carbon::now()->subMinutes(config('mstools.report.long_ttl')));      
                                 })->get();
        // If no expired reports found on the db layer, then find & delete physical reports aged more than <long_ttl> minutes if any                      
        if ($expiredReports->isEmpty()) {
            $files = array_merge(
                Storage::disk(config('mstools.report.disk'))->allFiles(config('mstools.report.directory')),
                Storage::disk(config('mstools.report.disk'))->allFiles("uploaded-".config('mstools.report.directory'))
            );
            foreach ($files as $file) {
                if (Storage::lastModified($file) < Carbon::now()->subMinutes(config('mstools.report.long_ttl'))->timestamp) {
                    app(FileUpload::class)->delete(config('mstools.report.disk'), $file);
                }
            }
        } else {
            foreach ($expiredReports as $report) {
                $report->delete();
            }
        }
    }
}
