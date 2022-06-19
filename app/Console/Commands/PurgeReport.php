<?php

namespace App\Console\Commands;

use App\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $expiredReports = Report::where('created_at', '<', Carbon::now()->subMinutes(config('mstools.report.ttl')))->get();
        foreach ($expiredReports as $report) {
            $report->delete();
        }
    }
}
