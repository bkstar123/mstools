<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Bkstar123\LaravelUploader\Contracts\FileUpload;

class PurgeDotNetCoreLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'netcorelog:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge .NET Core logs';

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
        $netcoreLogFiles = Storage::disk(config('mstools.netcorelog.disk'))->allFiles(config('mstools.netcorelog.directory'));
        foreach ($netcoreLogFiles as $file) {
            // Delete files whose last modified datetime > 5 minutes
            if (Storage::lastModified($file) < Carbon::now()->subMinutes(config('mstools.netcorelog.ttl'))->timestamp) {
                app(FileUpload::class)->delete(config('mstools.netcorelog.disk'), $file);
            }
        }
    }
}
