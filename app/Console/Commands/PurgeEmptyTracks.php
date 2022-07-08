<?php
/**
 * PurgeEmptyTracks Command
 *
 * @author: tuanha
 * @date: 08-July-2022
 */
namespace App\Console\Commands;

use App\Tracking;
use Illuminate\Console\Command;

class PurgeEmptyTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trackings:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge empty trackings';

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
        $emptyTrackings = Tracking::where('tracking_size', 0)->get();
        if (!empty($emptyTrackings)) {
            foreach ($emptyTrackings as $tracking) {
                $tracking->delete();
            }
        }
    }
}
