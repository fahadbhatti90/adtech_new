<?php

namespace App\Console\Commands\Ams\healthDashboard;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class populateHealthData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populateHealthData:dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to populate health dashboard data';

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
        Log::info("filePath:Commands\Ams\Health Dashboard. Start Cron.");
        Log::info($this->description);
        $date = date('Ymd');
        $currentDate = date('Ymd',  strtotime($date. ' - 1 day'));
        if (!empty($currentDate)){
            Log::info("current date = ".$currentDate);
            Log::info("sp master health dashboard called");
            $response = DB::connection("mysql")->select("CALL spMasterHealthDashboard (?)",[$currentDate]);
            Log::info("SP response". json_encode($response));
        }
    }
}
