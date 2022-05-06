<?php

namespace App\Console\Commands\Tacos;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\ams\ProfileModel;
use App\Models\Tacos\keywordList;
use App\Models\Tacos\TacosCronModel;
use App\Models\Tacos\TacosModel;
use App\Models\Tacos\TargetList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class tacosCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:tacos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to check tacos rule.';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     * @throws \ReflectionException
     */
    public function handle()
    {
        $currentDay = date('D');
        $nextDay = date('D', strtotime('+1 day', time()));
        $runStatus = false;
        $currentPm11 = date('H:i:s'); // 23:00
        $nextDayAM3 = date('H:i:s'); // 03:00
        $currentDayNow = date('Y-m-d');
        // check if day is wednesday, thursday and sunday
        if ($currentDay == 'Wed' || $currentDay == 'Thu' || $currentDay == 'Sun') {
            die;
        }
        // check if days is monday , tue , friday and saturday
        if ($currentDay == 'Mon' || $currentDay == 'Tue' || $nextDay == 'Tue') {
            if ($currentPm11 >= '23:00:00' && $currentDay == 'Mon') {
                $runStatus = true;
            } elseif ($currentDay == 'Tue' && $nextDayAM3 <= '03:00:00') {
                $runStatus = true;
            }
        } elseif ($currentDay == 'Fri' || $currentDay == 'Sat' || $nextDay == 'Sat') {
            if ($currentPm11 >= '23:00:00' && $currentDay == 'Fri') {
                $runStatus = true;
            } elseif ($currentDay == 'Sat' && $nextDayAM3 <= '03:00:00') {
                $runStatus = true;
            }
        }
        if ($runStatus == false) {
            die;
        }
        TacosCronModel::updateRecord();
        $dataTacosList = TacosModel::with('campaign:type,campaignType,profileId,fkConfigId,fkProfileId,campaignId')
            ->where('isActive', 1)
            ->get();
        if ($dataTacosList->isNotEmpty()) {
            foreach ($dataTacosList as $list) {
                if ($list->campaign == null) {
                    continue;
                }// if campaign info empty
                $existTacosCron = TacosCronModel::where('fkTacosId', $list->id)->get();
                if ($existTacosCron->isNotEmpty()) {
                    TacosCronModel::where('fkTacosId', $list->id)
                        ->update([
                            'type' => $list->campaign->type,
                            'profileId' => $list->campaign->profileId,
                            'fkConfigId' => $list->campaign->fkConfigId,
                            'campaignId' => $list->campaign->campaignId,
                            'sponsoredType' => $list->campaign->campaignType,
                            'lookBackPeriodDays' => 14,
                            'isActive' => $list->isActive
                        ]);
                } else {
                    $tacosCron = new TacosCronModel();
                    $tacosCron->fkTacosId = $list->id;
                    $tacosCron->type = $list->campaign->type;
                    $tacosCron->profileId = $list->campaign->profileId;
                    $tacosCron->fkConfigId = $list->campaign->fkConfigId;
                    $tacosCron->sponsoredType = $list->campaign->campaignType;
                    $tacosCron->campaignId = $list->campaign->campaignId;
                    $tacosCron->lookBackPeriodDays = 14;
                    $tacosCron->frequency = 0;
                    $tacosCron->isActive = 1;
                    $tacosCron->runStatus = 0;
                    $tacosCron->checkRule = 0;
                    $tacosCron->ruleResult = 0;
                    $tacosCron->currentRunTime = '0000-00-00 00:00:00';
                    $tacosCron->lastRunTime = '0000-00-00 00:00:00';
                    $tacosCron->nextRunTime = '0000-00-00 00:00:00';
                    $tacosCron->createdAt = date('Y-m-d H:i:s');
                    $tacosCron->updatedAt = date('Y-m-d H:i:s');
                    $tacosCron->save();
                } // if already Tacos id inserted
            }//endforeach
        }//endif
        $responseDataCron = TacosCronModel::with('getTokenDetail')
            ->where('isActive', 1)
            ->get(); //get all record from DB
        if ($responseDataCron->isNotEmpty()) {
            foreach ($responseDataCron as $singleDataArray) {
                if ($singleDataArray->getTokenDetail == null) {
                    continue;
                }// if campaign info empty jump on another
                $singleData = TacosCronModel::with('getTokenDetail')
                    ->where('isActive', 1)
                    ->where('id', $singleDataArray->id)
                    ->first(); //get one record from DB
                if (empty($singleData)) {
                    continue; //jump on another
                }// if campaign info empty
                $id = $singleData->id;
                $lookBackPeriodDays = $singleData->lookBackPeriodDays;
                $sponsoredType = $singleData->sponsoredType;
                $type = $singleData->type;
                $fkTacosId = $singleData->fkTacosId;
                $frequency = $singleData->frequency;
                $hourlyCheckCurrentTimeNow = date('H');
                $CronTime = $singleData->currentRunTime;
                $hourlyCheckCronTime = '';
                if ($CronTime == '0000-00-00 00:00:00') {
                    $CronTime = date('Y-m-d H');
                    $hourlyCheckCronTime = date('H');
                } else {
                    $CronTime = date('Y-m-d H', strtotime($singleData->currentRunTime));
                    $hourlyCheckCronTime = date('H', strtotime($singleData->currentRunTime));
                }
                $CronLastRun = $singleData->lastRunTime;
                $CronLastRunHourMinuteSec = '';
                // check last cron time
                if ($CronLastRun == '0000-00-00 00:00:00') {
                    $CronLastRunHourMinuteSec = date('Y-m-d H:i:s');
                    $CronLastRun = date('Y-m-d H', strtotime($CronLastRunHourMinuteSec));
                } else {
                    $CronLastRunHourMinuteSec = date('Y-m-d H:i:s', strtotime($CronLastRun));
                    $CronLastRun = date('Y-m-d H', strtotime($CronLastRunHourMinuteSec));
                }
                // next cron run time
                $nextRunTime = $singleData->nextRunTime;
                $TodayNextRun = date('Y-m-d', strtotime($singleData->nextRunTime));
                if ($nextRunTime == '0000-00-00 00:00:00') {
                    if ($currentDay == 'Mon' || $currentDay == 'Tue') {
                        $nextRunTime = date('Y-m-d H:i:s', strtotime('+4 day', time()));
                        $TodayNextRun = date('Y-m-d', strtotime('+3 day', time()));

                    } elseif ($currentDay == 'Fri' || $currentDay == 'Sat') {
                        $nextRunTime = date('Y-m-d H:i:s', strtotime('+3 day', time()));
                        $TodayNextRun = date('Y-m-d', strtotime('+3 day', time()));
                    }
                    if ($currentDayNow != $TodayNextRun) {
                        $TodayNextRun = $currentDayNow;
                    }
                }
                if ($currentDayNow > $TodayNextRun) {
                    if ($currentDay == 'Sat' && $nextDayAM3 <= '03:00:00') {
                        $TodayNextRun = date('Y-m-d');
                    }
                    if ($currentDay == 'Tue' && $nextDayAM3 <= '03:00:00') {
                        $TodayNextRun = date('Y-m-d');
                    }
                }
                if ($singleData->runStatus == 0 && $frequency < 3 && $nextRunTime > $CronLastRunHourMinuteSec && $currentDayNow <= $TodayNextRun) {
                    $this->checkTimeAbove3();
                    $this->RunCronFrequencyVise($singleData);
                } elseif ($singleData->runStatus == 1 && $frequency == 2) { // change cronRun status again 0
                    $updateArray = array(
                        'updatedAt' => date('Y-m-d H:i:s'),
                        'runStatus' => '0',
                        'frequency' => '0',
                        'checkRule' => '0',
                        'ruleResult' => '0',
                        'emailSent' => '0',
                        'isData' => '0'
                    );
                    TacosCronModel::where('fkTacosId', $fkTacosId)->update($updateArray);
                } elseif ($singleData->runStatus == 1 && $frequency >= 1 && $currentDayNow >= $TodayNextRun && $nextRunTime > $CronLastRunHourMinuteSec) {
                    $updateArray = array(
                        'updatedAt' => date('Y-m-d H:i:s'),
                        'runStatus' => '0',
                        'frequency' => '0',
                        'checkRule' => '0',
                        'ruleResult' => '0',
                        'emailSent' => '0',
                        'isData' => '0'
                    );
                    TacosCronModel::where('fkTacosId', $fkTacosId)->update($updateArray);
                }
            }// end foreach
        }// endif
    }

    /**
     * @param $dataArray
     */
    private function RunCronFrequencyVise($dataArray)
    {
        ob_start();
        $DB1 = 'mysql'; // layer 0 database
        $currentDay = date('D');
        $nextRunDayTime = date('Y-m-d H:i:s');
        if ($currentDay == 'Mon' || $currentDay == 'Tue') {
            $nextRunDayTime = date('Y-m-d H:i:s', strtotime('+4 day', time()));
            if ($currentDay == 'Tue') {
                $nextRunDayTime = date('Y-m-d H:i:s', strtotime('+3 day', time()));
            }
        } elseif ($currentDay == 'Fri' || $currentDay == 'Sat') {
            $nextRunDayTime = date('Y-m-d H:i:s', strtotime('+3 day', time()));
            if ($currentDay == 'Sat') {
                $nextRunDayTime = date('Y-m-d H:i:s', strtotime('+2 day', time()));
            }
        }
        $fkTacosId = $dataArray->fkTacosId;
        $campaignId = $dataArray->campaignId;
        $type = $dataArray->type;
        $lookBackPeriodDays = $dataArray->lookBackPeriodDays;
        $frequency = ($dataArray->frequency == 1) ? 2 : 1;
        $updateArray = array(
            'currentRunTime' => date('Y-m-d H:i:s'),
            'lastRunTime' => date('Y-m-d H:i:s'),
            'nextRunTime' => $nextRunDayTime,
            'runStatus' => '1',
            'frequency' => $frequency,
        );
        TacosCronModel::where('fkTacosId', $fkTacosId)->update($updateArray);
        //send notification for tacos cron job start
        $notificationData = $this->cronJobNotificationData($dataArray, 'tacosCronJobStarted');
        if (!empty($notificationData)) {
            $addNotification = new AmsAlertNotificationsController();
            $addNotification->addAlertNotification($notificationData);
        }
        Artisan::call('keywordlist:tacos', ['array' => [$dataArray]]);
        $this->checkTimeAbove3();
        if ($type != 'SD') {
            // only use SP and SB keyword bid Values
            $responseData = keywordList::with('getConfigId')
                ->where(['fkTacosId' => $fkTacosId])
                ->where(['reportType' => $type])
                ->get();
            if ($responseData->isNotEmpty()) {
                $keywordIdArray = array(); // keyword ID string conversation
                foreach ($responseData as $singleKeywordId) {
                    $keywordIdArray[] = $singleKeywordId->keywordId;
                } //endforeach
                $parameter = array(
                    $campaignId,
                    "'" . implode(',', $keywordIdArray) . "'",
                    $type,
                    (int)$lookBackPeriodDays,
                    'keyword');
                $KeywordReportData = \DB::connection($DB1)->select("CALL spAMSTacos(?,?,?,?,?)", $parameter);
                if (!empty($KeywordReportData)) {
                    TacosCronModel::where('fkTacosId', $fkTacosId)->update(['checkRule' => 1, 'updatedAt' => date('Y-m-d H:i:s')]);
                    foreach ($KeywordReportData as $reportKeywords) {
                        if ($reportKeywords->clicks == 0) { // if clicks = 0 do nothing
                            continue;
                        }// end if
                        $keywordBidData = keywordList::with('getConfigId')
                            ->where(['keywordId' => $reportKeywords->keywordId])
                            ->first();
                        if ($keywordBidData) {
                            if ($keywordBidData->getConfigId == null) {
                                continue;
                            }
                            // check implement
                            $this->rulesLogics($fkTacosId, $reportKeywords, $keywordBidData);
                        }// end if
                    }// end foreach
                }// end if
            } else {
                Log::info('keyword data not found.');
            }
        } elseif ($type == 'SD') {
            // only use for target SD
            $responseData = TargetList::with('getConfigId')
                ->where(['fkTacosId' => $fkTacosId])
                ->get();
            if ($responseData->isNotEmpty()) {
                $targetIdArray = array(); // target ID string conversation
                foreach ($responseData as $singleTargetId) {
                    $targetIdArray[] = $singleTargetId->targetId;
                }
                $parameter = array(
                    $campaignId,
                    "'" . implode(',', $targetIdArray) . "'",
                    '',
                    (int)$lookBackPeriodDays,
                    'target');
                $targetReportData = \DB::connection($DB1)->select("CALL spAMSTacos(?,?,?,?,?)", $parameter);
                if (!empty($targetReportData)) {
                    TacosCronModel::where('fkTacosId', $fkTacosId)->update(['checkRule' => 1, 'updatedAt' => date('Y-m-d H:i:s')]);
                    foreach ($targetReportData as $reportTarget) {
                        if ($reportTarget->clicks == 0) { // if clicks = 0 do nothing
                            continue;
                        }// end if
                        $targetBidData = TargetList::with('getConfigId')
                            ->where(['targetId' => $reportTarget->targetId])
                            ->first();
                        if ($targetBidData) {
                            if ($targetBidData->getConfigId == null) {
                                continue;
                            }
                            // check implement
                            $this->rulesLogicsTarget($fkTacosId, $reportTarget, $targetBidData);
                        }// end if
                    }// end foreach
                }// end if
            }// end if
            Log::info('Target data not found.');
        }
        //send notification for tacos cron job end
        $notificationData = $this->cronJobNotificationData($dataArray, 'tacosCronJobCompleted');
        if (!empty($notificationData)) {
            $addNotification = new AmsAlertNotificationsController();
            $addNotification->addAlertNotification($notificationData);
        }
        ob_end_flush();
    }

    /**
     * This function is used to validate day and time
     */
    private function checkTimeAbove3()
    {
        $currentDay = date('D');
        $nextDay = date('D', strtotime('+1 day', time()));
        // check if day change and it time is less 3:00 am then it will not run
        if ($currentDay == 'Mon' || $currentDay == 'Tue' || $nextDay == 'Tue') {
            if ($currentDay == 'Tue' && date('H:i:s') >= '03:00:00') {
                die;
            }
        } elseif ($currentDay == 'Fri' || $currentDay == 'Sat' || $nextDay == 'Sat') {
            if ($currentDay == 'Sat' && date('H:i:s') >= '03:00:00') {
                die;
            }
        }
    }

    /**
     * This function is used to calculate values
     *
     * @param $fkTacosId
     * @param $reportKeywords
     * @param $keywordBidData
     */
    private function rulesLogics($fkTacosId, $reportKeywords, $keywordBidData)
    {
        $fkConfigId = $keywordBidData->fkConfigId;
        $client_id = $keywordBidData->getConfigId->client_id;
        $adGroupId = $keywordBidData->adGroupId;
        $state = $keywordBidData->state;
        $reportType = $keywordBidData->reportType;
        $keywordBid = $keywordBidData->bid;
        $keywordId = $reportKeywords->keywordId;
        $clicks = $reportKeywords->clicks;
        $acos = $reportKeywords->acos;
        $cpc = $reportKeywords->cpc;
        $roas = $reportKeywords->roas;
        $fkProfileId = $reportKeywords->fkProfileId;
        $campaignName = $reportKeywords->campaignName;
        Log::info("-------------------Keyword------------------------");
        $Rule = TacosModel::where('id', $fkTacosId)->get()->first();
        if ($Rule) {
            $profileId = $Rule->profileId;
            $campaignId = $Rule->campaignId;
            $metric = $Rule->metric;
            $bid_min = $Rule->min;
            $bid_max = $Rule->max;
            $tacos = $Rule->tacos;
            Log::info('keywordId ' . $keywordId);
            Log::info('fkTacosId ' . $fkTacosId);
            Log::info('acos ' . $acos);
            Log::info('cpc ' . $cpc);
            Log::info('roas ' . $roas);
            Log::info('clicks ' . $clicks);
            Log::info('profile ' . $profileId);
            Log::info('campaignId ' . $campaignId);
            Log::info('tacos ' . $tacos);
            Log::info('metric ' . $metric);
            Log::info('bid_min ' . $bid_min);
            Log::info('bid_max ' . $bid_max);
            Log::info('keywordBid ' . $keywordBid);
            switch ($metric) {
                case 'acos':
                    if ($acos < $tacos) {
                        $variance = round(abs(1 - ($acos / $tacos)) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->increaseBid($metric, $acos, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $keywordBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'keywordId' => $keywordId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $keywordBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateKeywordbid:tacos', $data);
                    } elseif ($acos > $tacos) {
                        $variance = round(abs(($acos / $tacos) - 1) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->decreaseBid($metric, $acos, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $keywordBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'keywordId' => $keywordId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $keywordBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateKeywordbid:tacos', $data);
                    }
                    break;
                case 'roas':
                    if ($roas > $tacos) {
                        $variance = round(abs(1 - ($roas / $tacos)) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->increaseBid($metric, $roas, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $keywordBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'keywordId' => $keywordId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $keywordBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateKeywordbid:tacos', $data);
                    } elseif ($roas < $tacos) {
                        $variance = round(abs(($roas / $tacos) - 1) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->decreaseBid($metric, $roas, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $keywordBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'keywordId' => $keywordId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $keywordBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateKeywordbid:tacos', $data);
                    }
                    break;
                default:
                    echo 'not metric selected';
            }
        }// end if
        Log::info('-------------------------------------------');
    }

    /**
     * @param $fkTacosId
     * @param $reportTarget
     * @param $targetBidData
     */
    private function rulesLogicsTarget($fkTacosId, $reportTarget, $targetBidData)
    {
        $fkConfigId = $targetBidData->fkConfigId;
        $client_id = $targetBidData->getConfigId->client_id;
        $adGroupId = $targetBidData->adGroupId;
        $state = $targetBidData->state;
        $reportType = $targetBidData->reportType;
        $currentBid = $targetBidData->bid;
        $targetId = $reportTarget->targetId;
        $clicks = $reportTarget->clicks;
        $acos = $reportTarget->acos;
        $cpc = $reportTarget->cpc;
        $roas = $reportTarget->roas;
        $fkProfileId = $reportTarget->fkProfileId;
        $campaignName = $reportTarget->campaignName;
        Log::info("------------------Target-------------------------");
        $Rule = TacosModel::where('id', $fkTacosId)->get()->first();
        if ($Rule) {
            $profileId = $Rule->profileId;
            $campaignId = $Rule->campaignId;
            $metric = $Rule->metric;
            $bid_min = $Rule->min;
            $bid_max = $Rule->max;
            $tacos = $Rule->tacos;
            Log::info('targetId ' . $targetId);
            Log::info('fkTacosId ' . $fkTacosId);
            Log::info('acos ' . $acos);
            Log::info('cpc ' . $cpc);
            Log::info('roas ' . $roas);
            Log::info('clicks ' . $clicks);
            Log::info('profile ' . $profileId);
            Log::info('campaignId ' . $campaignId);
            Log::info('tacos ' . $tacos);
            Log::info('metric ' . $metric);
            Log::info('bid_min ' . $bid_min);
            Log::info('bid_max ' . $bid_max);
            Log::info('currentBid ' . $currentBid);
            switch ($metric) {
                case 'acos':
                    if ($acos < $tacos) {
                        $variance = round(abs(1 - ($acos / $tacos)) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->increaseBid($metric, $acos, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'targetId' => $targetId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $currentBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateTargetbid:tacos', $data);
                    } elseif ($acos > $tacos) {
                        $variance = round(abs(($acos / $tacos) - 1) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->decreaseBid($metric, $acos, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'targetId' => $targetId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $currentBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateTargetbid:tacos', $data);
                    }
                    break;
                case 'roas':
                    if ($roas > $tacos) {
                        $variance = round(abs(1 - ($roas / $tacos)) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->increaseBid($metric, $roas, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'targetId' => $targetId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $currentBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateTargetbid:tacos', $data);
                    } elseif ($roas < $tacos) {
                        $variance = round(abs(($roas / $tacos) - 1) * 100, 2);
                        Log::info('variance ' . $variance);
                        $newBidValue = $this->decreaseBid($metric, $roas, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid);
                        Log::info('newBidValue ' . $newBidValue);
                        $data['data'] = array(
                            'fkTacosId' => $fkTacosId,
                            'profileId' => $profileId,
                            'fkConfigId' => $fkConfigId,
                            'clientId' => $client_id,
                            'campaignId' => $campaignId,
                            'adGroupId' => $adGroupId,
                            'targetId' => $targetId,
                            'state' => $state,
                            'reportType' => $reportType,
                            'oldbid' => $currentBid,
                            'newbid' => $newBidValue,
                            'fkProfileId' => $fkProfileId,
                            'campaignName' => $campaignName
                        );
                        Artisan::call('updateTargetbid:tacos', $data);
                    }
                    break;
                default:
                    echo 'not metric selected';
            }
        }// end if
        Log::info('-------------------------------------------');
    }

    /**
     * @param $metric
     * @param $metricValue
     * @param $tacos
     * @param $cpc
     * @param $variance
     * @param $clicks
     * @param $bid_min
     * @param $bid_max
     * @param $currentBid
     * @return float
     */
    private function increaseBid($metric, $metricValue, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid)
    {
        $newBid = 0;
        switch ($metric) {
            case 'acos':
                if ($variance >= 10 && $variance <= 20 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((5 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 10 && $variance <= 20 && $clicks >= 10) {
                    $newBid = abs(abs(((10 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((15 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 10) {
                    $newBid = abs(abs(((20 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((30 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 10) {
                    $newBid = abs(abs(((40 / 100) * $currentBid) + $currentBid));
                    if ($metricValue < $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue < $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                }
                break;
            case 'roas':
                if ($variance >= 10 && $variance <= 20 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((5 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 10 && $variance <= 20 && $clicks >= 10) {
                    $newBid = abs(abs(((10 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((15 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 10) {
                    $newBid = abs(abs(((20 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((30 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 10) {
                    $newBid = abs(abs(((40 / 100) * $currentBid) + $currentBid));
                    if ($metricValue > $tacos && $bid_max > 0) {
                        if ($newBid > $bid_max) {
                            $newBid = $bid_max;
                        }
                    } elseif ($metricValue > $tacos && $bid_max == 0) {
                        if ($newBid > 3 * $cpc) {
                            $newBid = 3 * $cpc;
                        }
                    }
                }
                break;
            default:
                echo 'not metric selected in increase section';
        }
        return round($newBid, 2);
    }

    /**
     * @param $metric
     * @param $metricValue
     * @param $tacos
     * @param $cpc
     * @param $variance
     * @param $clicks
     * @param $bid_min
     * @param $bid_max
     * @param $currentBid
     * @return float
     */
    private function decreaseBid($metric, $metricValue, $tacos, $cpc, $variance, $clicks, $bid_min, $bid_max, $currentBid)
    {
        $newBid = 0;
        switch ($metric) {
            case 'acos':
                if ($variance >= 10 && $variance <= 20 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((5 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 10 && $variance <= 20 && $clicks >= 10) {
                    $newBid = abs(abs(((10 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((15 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 10) {
                    $newBid = abs(abs(((20 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((30 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 10) {
                    $newBid = abs(abs(((40 / 100) * $currentBid) - $currentBid));
                    if ($metricValue > $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                }
                break;
            case 'roas':
                if ($variance >= 10 && $variance <= 20 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((5 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue < $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 10 && $variance <= 20 && $clicks >= 10) {
                    $newBid = abs(abs(((10 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue < $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((15 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue > $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance >= 20.1 && $variance <= 40 && $clicks >= 10) {
                    $newBid = abs(abs(((20 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue < $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 1 && $clicks <= 9) {
                    $newBid = abs(abs(((30 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue < $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                } elseif ($variance > 40.1 && $clicks >= 10) {
                    $newBid = abs(abs(((40 / 100) * $currentBid) - $currentBid));
                    if ($metricValue < $tacos && $bid_min > 0) {
                        if ($newBid < $bid_max) {
                            $newBid = $bid_min;
                        }
                    } elseif ($metricValue < $tacos && $bid_min == 0) {
                        if ($newBid > $currentBid) {
                            $newBid = $currentBid;
                        }
                    }
                }
                break;
            default:
                echo 'not metricValue selected in descrease section';
        }
        return round($newBid, 2);
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function cronJobNotificationData($data, $type)
    {
        $profileId = $data->profileId;
        $getFkProfileID = ProfileModel::select('id')
            ->where('profileId', $profileId)
            ->first();
        $fkTacosId = $data->fkTacosId;
        $fkProfileId = $getFkProfileID->id;
        $notificationData = [];
        switch ($type) {
            case "tacosCronJobStarted":
                $notificationTitle = "Tacos Cron Job Started.";
                $notificationMessage = "Tacos Cron Job Tacos Id: " . $fkTacosId . " Started";
                $notificationData['time'] = date('Y-m-d H:i:s');
                break;
            case "tacosCronJobCompleted":
                $notificationTitle = "Tacos Cron Job Completed.";
                $notificationMessage = "Tacos Cron Job Tacos Id: " . $fkTacosId . " Completed";
                $notificationData['time'] = date('Y-m-d H:i:s');
                break;
        }
        $notificationData['fkTacosId'] = $fkTacosId;
        $notificationData['type'] = $type;
        $notificationData['moduleName'] = "tacos";
        $notificationData['notificationTitle'] = $notificationTitle;
        $notificationData['notificationMessage'] = $notificationMessage;
        $notificationData['fkProfileId'] = $fkProfileId;
        $notificationData['sendEmail'] = 1;
        return $notificationData;
    }
}
