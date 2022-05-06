<?php

namespace App\Console\Commands;

use App\Models\ams\profileReportStatus;
use Artisan;
use App\Models\AMSModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App\Models\ams\Report\Link\AmsFailedReportsLinks;
use Illuminate\Support\Facades\Log;

class AmsCronJobList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amscronjobs:cronlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command run every minute and check coming ams cron job.';

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
        $currentTimeNow = date('H');
        $currentRunTime = date('Y-m-d H:i:s');
        $CronArrayResponse = AMSModel::getAllEnabledCronList();
        if ($CronArrayResponse != false) {
            // get enable cron lists
            AMSModel::insertTrackRecord('get enable crons list', 'record found');
            foreach ($CronArrayResponse as $singleCron) {
                // create variable CronRun
                $cronRunStaus = $singleCron->cronRun;
                // create variable cronType
                $cronType = $singleCron->cronType;
                // convert cron into hour
                $CronTime = date('H', strtotime($singleCron->cronTime));
                // create variable for last Time Cron
                $CronLastRun = $singleCron->lastRun;
                // check last cron time
                if ($CronLastRun == '0000:00:00 00:00:00') {
                    $lastDateTimeFormat = date('Y-m-d H:i:s', strtotime('-1 day', time()));
                    $CronLastRun = date('Y-m-d H', strtotime($lastDateTimeFormat));
                } else {
                    $CronLastRun = date('Y-m-d H', strtotime($CronLastRun));
                }
                // create variable for Next Time Cron
                $nextRunTime = $singleCron->nextRunTime;
                // check Next Cron Time is not NA
                if ($nextRunTime == '0000:00:00 00:00:00') {
                    $nextRunTime = date('Y-m-d H:i:s', strtotime('+1 day', time()));
                    $nextRunTime = date('Y-m-d H', strtotime($nextRunTime));
                } else {
                    $nextRunTime = date('Y-m-d H', strtotime($nextRunTime));
                    // if next time is greater than last time
                    if ($CronLastRun > $nextRunTime) {
                        $nextRunTime = date('Y-m-d H:i:s', strtotime('+1 day', time()));
                        $nextRunTime = date('Y-m-d H', strtotime($nextRunTime));
                    }
                }
                // currently Retort Status
                $checkReportStatus = \DB::table('tbl_ams_crons')->where('cronType', $singleCron->cronType)->get()->first();
                if (empty($checkReportStatus)) {
                    Log::info('tbl_ams_crons table is empty.');
                }
                // check Current system Time equal to Cron Set Time
                // Check Last run cron time less than coming next Cron time
                if ($CronTime == $currentTimeNow && $CronLastRun < $nextRunTime && $checkReportStatus->cronRun == 0) {
                    // tracker code
                    AMSModel::insertTrackRecord('got enabled crons type ' . $cronType, 'record found');
                    // call function gathering api data
                    $this->innerFunction($singleCron);
                } elseif ($cronRunStaus == 1 && $CronTime < $currentTimeNow) { // change cronRun status again 0
                    // tracker code
                    AMSModel::insertTrackRecord('change enabled crons type ' . $cronType, 'success');
                    Log::info('start update query for update CronRun status to 0');
                    $updateArray = array(
                        'modifiedDate' => date('Y-m-d H:i:s'),
                        'cronRun' => '0',
                    );
                    AMSModel::updateCronRunStatus($cronType, $updateArray);
                    Log::info('end update query for update CronRun status to 0');
                } else {
                    Log::info('Currently no cron time occur.');
                }
            }// end foreach loop
            Log::info('End foreach loop');
        } else {
            Log::info('not record found');
        }
        Log::info('End Cron for AMS');
    }

    /**
     * This function is used to run until cron status '1'
     *
     * @param $data
     * @return mixed
     */
    private function innerFunction($data)
    {
        $DBArray = \DB::table('tbl_ams_crons')->where('cronType', $data->cronType)->get()->first();
        if ($DBArray->cronRun == 0) {
            // update cron status when it done on time
            // Create variable for Report Type
            $ReportType = $data->cronType;
            Log::info('Start Switch');
            switch ($ReportType) {
                case "Advertising_Campaign_Reports":
                    $attempt = 1;
                    a1:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SP.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        // First Get Report Id
                        Artisan::call('getcampaignreportid:spcampaign');
                        // Second Get Report Link
                        Artisan::call('getcampaignreportlink:spcampaign');
                        // Third Get Report Data From Link
                        Artisan::call('getcampaignreportlinkdata:spcampaign');
                        // Forth Get bid value of keyword via Campaign
                        // This command is commented as per discussion.
                        //Its not use in bidding rule.And needed to handle large amount of data
                        /*Artisan::call('keywordBid:keywordBidSP');*/
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SP.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Campaign_SP');
                            Log::info('Report Type : ' . $ReportType . '-Campaign_SP.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a1;
                    }
                    break;
                case "Ad_Group_Reports":
                    $attempt = 1;
                    a2:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SP.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getadgroupreportid:spadgroup');
                        // Second Get Report Link
                        Artisan::call('getadgroupreportlink:spadgroup');
                        // Third Get Report Data From Link
                        Artisan::call('getadgroupreportlinkdata:spadgroup');
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SP.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('AdGroup_SP');
                            Log::info('Report Type : ' . $ReportType . '-AdGroup_SP.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a2;
                    }
                    break;
                case "Keyword_Reports":
                    $attempt = 1;
                    a3:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Keyword_SP.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getkeywordreportid:spkeyword');
                        // Second Get Report Link
                        Artisan::call('getkeywordreportlink:spkeyword');
                        // Third Get Report Data From Link
                        Artisan::call('getkeywordreportlinkdata:spkeyword');
                        Log::info('Report Type Cron : ' . $ReportType . '-Keyword_SP.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Keyword_SP');
                            Log::info('Report Type : ' . $ReportType . '-Keyword_SP.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a3;
                    }
                    break;
                case "Product_Ads_Report":
                    $attempt = 1;
                    a4:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Ads.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getproductsadsreportid:productsads');
                        // Second Get Report Link
                        Artisan::call('getproductsadsreportlink:productsads');
                        // Third Get Report Data From Link
                        Artisan::call('getproductsadsreportlinkdata:productsads');
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Ads.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Product_Ads');
                            Log::info('Report Type : ' . $ReportType . '-Product_Ads.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a4;
                    }
                    break;
                case "ASINs_Report":
                    $attempt = 1;
                    a5:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-ASINs.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getASINreport:asinreport');
                        // Second Get Report Link
                        Artisan::call('getasinreportlink:asinreport');
                        // Third Get Report Data From Link
                        Artisan::call('getasinreportlinkdata:asinreport');
                        Log::info('Report Type Cron : ' . $ReportType . '-ASINs.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('ASINs');
                            Log::info('Report Type : ' . $ReportType . '-ASINs.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a5;
                    }
                    break;
                case "Product_Attribute_Targeting_Reports":
                    $attempt = 1;
                    a6:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('gettargetreportid:targets');
                        // Second Get Report Link
                        Artisan::call('gettargetreportlink:targets');
                        // Third Get Report Data From Link
                        Artisan::call('gettargetreportlinkdata:targets');
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Product_Targeting');
                            Log::info('Report Type : ' . $ReportType . '-Product_Targeting.Delete Error ten.Attempt =' . $attempt);
                            $attempt++;
                            goto a6;
                        }
                    }
                    break;
                case "Sponsored_Brand_Reports": // keyword SB reports
                    $attempt = 1;
                    a7:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Keyword_SB.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getkeywordreportid:sbkeyword');
                        // Second Get Report Link
                        Artisan::call('getkeywordreportlink:sbkeyword');
                        // Third Get Report Data From Link
                        Artisan::call('getkeywordreportlinkdata:sbkeyword');
                        Log::info('Report Type Cron : ' . $ReportType . '-Keyword_SB.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Keyword_SB');
                            Log::info('Report Type : ' . $ReportType . '-Keyword_SB.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a7;
                    }
                    break;
                case "Sponsored_Brand_Campaigns":
                    $attempt = 1;
                    a8:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SB.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getcampaignreportid:sbcampaign');
                        // Second Get Report Link
                        Artisan::call('getcampaignreportlink:sbcampaign');
                        // Third Get Report Data From Link
                        Artisan::call('getcampaignreportlinkdata:sbcampaign');
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SB.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Campaign_SB');
                            Log::info('Report Type : ' . $ReportType . '-Campaign_SB.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a8;
                    }
                    break;
                case "Sponsored_Display_Campaigns":
                    $attempt = 1;
                    a9:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SD.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getcampaignreportid:sdcampaign');
                        // Second Get Report Link
                        Artisan::call('getcampaignreportlink:sdcampaign');
                        // Third Get Report Data From Link
                        Artisan::call('getcampaignreportlinkdata:sdcampaign');
                        Log::info('Report Type Cron : ' . $ReportType . '-Campaign_SD.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Campaign_SD');
                            Log::info('Report Type : ' . $ReportType . '-Campaign_SD.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a9;
                    }
                    break;
                case "Sponsored_Display_Adgroup":
                    $attempt = 1;
                    a10:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SD.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getsdadgroupreportid:sdadgroup');
                        // Second Get Report Link
                        Artisan::call('getsdadgroupreportlink:sdadgroup');
                        // Third Get Report Data From Link
                        Artisan::call('getsdadgroupreportlinkdata:sdadgroup');
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SD.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('AdGroup_SD');
                            Log::info('Report Type : ' . $ReportType . '-AdGroup_SD.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a10;
                    }
                    break;
                case "Sponsored_Display_ProductAds":
                    $attempt = 1;
                    a11:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-SD_Product_Ads.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getsdproductsadsreportid:sdproductsads');
                        // Second Get Report Link
                        Artisan::call('getsdproductsadsreportlink:sdproductsads');
                        // Third Get Report Data From Link
                        Artisan::call('getsdproductsadsreportlinkdata:sdproductsads');
                        Log::info('Report Type Cron : ' . $ReportType . '-SD_Product_Ads.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('SD_Product_Ads');
                            Log::info('Report Type : ' . $ReportType . '-SD_Product_Ads.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a11;
                    }
                    break;
                case "Sponsored_Brand_Adgroup":
                    $attempt = 1;
                    a12:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SB.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('getsbadgroupreportid:sbadgroup');
                        // Second Get Report Link
                        Artisan::call('getsbadgroupreportlink:sbadgroup');
                        // Third Get Report Data From Link
                        Artisan::call('getsbadgroupreportlinkdata:sbadgroup');
                        Log::info('Report Type Cron : ' . $ReportType . '-AdGroup_SB.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('AdGroup_SB');
                            Log::info('Report Type : ' . $ReportType . '-AdGroup_SB.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a12;
                    }
                    break;
                case "Sponsored_Brand_Targeting":
                    $attempt = 1;
                    a13:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting_SB.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('gettargetreportid:sbtargets');
                        // Second Get Report Link
                        Artisan::call('gettargetreportlink:sbtargets');
                        // Third Get Report Data From Link
                        Artisan::call('gettargetreportlinkdata:sbtargets');
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting_SB.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Product_Targeting_SB');
                            Log::info('Report Type : ' . $ReportType . '-Product_Targeting_SB.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a13;
                    }
                    break;
                case "Target_Report_SD":
                    $attempt = 1;
                    a14:
                    if ($attempt <= 2) {
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting_SD.Cron Started.Attempt =' . $attempt);
                        $this->updateCronStatusRun($ReportType, 1);
                        //commands
                        // First Get Report Id
                        Artisan::call('gettargetreportid:sdtargets');
                        // Second Get Report Link
                        Artisan::call('gettargetreportlink:sdtargets');
                        // Third Get Report Data From Link
                        Artisan::call('gettargetreportlinkdata:sdtargets');
                        Log::info('Report Type Cron : ' . $ReportType . '-Product_Targeting_SD.Cron End.Attempt =' . $attempt);
                        $this->updateCronStatusStop($ReportType, 0);
                        if ($attempt < 2) {
                            $deleteErrorTen = $this->deleteErrorTen('Product_Targeting_SD');
                            Log::info('Report Type : ' . $ReportType . '-Product_Targeting_SD.Delete Error ten.Attempt =' . $attempt);
                        }
                        $attempt++;
                        goto a14;
                    }
                    break;
                default:
                    Log::info('Report not selected.');
            }// end switch statement
            Log::info('End Switch');
        } else {
            // tracker code
            AMSModel::insertTrackRecord('got enabled crons status : 1', 'record found');
            Log::info('cron status is 1');
        }
    }

    /**
     *   deleteAmsFailedLinkReportId
     * @param $reportType
     * @param $cronStatusRun
     * @return Null
     */
    private function updateCronStatusRun($ReportType, $cronStatusRun)
    {
        Log::info('Report Type Cron : ' . $ReportType . '.Start update cron run status=1');
        $updateArray = array(
            'lastRun' => date('Y-m-d H:i:s'),
            'nextRunTime' => date('Y-m-d H:i:s', strtotime('+1 day', time())),
            'modifiedDate' => date('Y-m-d H:i:s'),
            'cronRun' => $cronStatusRun,
        );
        $res = AMSModel::updateCronRunStatus($ReportType, $updateArray);
        // tracker code
        AMSModel::insertTrackRecord('Report Type Cron : ' . $ReportType . '.Update cron run status=1', 'General Message');
        Log::info('Report Type Cron : ' . $ReportType . '.End update cron run status=1');
        return $res;
    }

    /**
     *   deleteAmsFailedLinkReportId
     * @param $reportType
     * @param $cronStatusStop
     * @return Null
     */
    private function updateCronStatusStop($ReportType, $cronStatusStop)
    {
        Log::info('Report Type Cron : ' . $ReportType . '.Start update cron run status=0');
        $updateArray = array(
            'nextRunTime' => date('Y-m-d H:i:s', strtotime('+1 day', time())),
            'modifiedDate' => date('Y-m-d H:i:s'),
            'cronRun' => $cronStatusStop,
        );
        $res = AMSModel::updateCronRunStatus($ReportType, $updateArray);
        // tracker code
        AMSModel::insertTrackRecord('Report Type Cron : ' . $ReportType . '.Update cron run status=0', 'General Message');
        Log::info('Report Type Cron : ' . $ReportType . '.End update cron run status=0');
        return $res;
    }

    /**
     *   checkFailedReportCount
     * @param  $reportType
     * @return integer
     */
    private function checkFailedReportCount($reportType)
    {
        $reportDateSingleDay = date('Ymd', strtotime('- 1 day', time()));
        $res = AmsFailedReportsLinks::where('reportType', $reportType)->where('reportDate', $reportDateSingleDay)->count();
        return $res;
    }

    /**
     *   checkFailedReportCount
     * @param  $reportType
     * @return integer
     */
    private function deleteErrorTen($reportType)
    {
        $currentDay = 0;
        $currentDayTime = date('Y-m-d', strtotime($currentDay . ' day', time()));
        $res = false;
        if (profileReportStatus::where('adType', $reportType)->where('status', 10)->where('created_at', 'like', $currentDayTime . '%')->count() > 0) {
            $res = profileReportStatus::where('adType', $reportType)->where('status', 10)->where('created_at', 'like', $currentDayTime . '%')->delete();
        }
        $res;
    }
}