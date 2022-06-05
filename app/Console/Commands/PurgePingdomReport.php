<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class PurgePingdomReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pingdomreport:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge Pingdom reports';

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
        $pingdomReportFiles = Storage::disk(config('mstools.pingdomreport.disk'))->allFiles(config('mstools.pingdomreport.directory'));
        foreach ($pingdomReportFiles as $file) {
            // Delete files whose last modified datetime > 5 minutes
            if (Storage::lastModified($file) < Carbon::now()->subMinutes(config('mstools.pingdomreport.ttl'))->timestamp) {
                app(FileUpload::class)->delete(config('mstools.pingdomreport.disk'), $file);
            }
        }
    }
}
