<?php

namespace App\Console\Commands\BiddingRule;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Mail\BuyBoxEmailAlertMarkdown;
use App\Models\AccountModels\AccountModel;
use App\Models\ams\Target\BiddingRule\TargetList;
use App\Models\BiddingRule;
use App\Models\ClientModels\ClientModel;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class cron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biddingRule:cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to check bidding rule.';

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
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     * @throws \ReflectionException
     */
    public function handle()
    {
        Log::info("filePath:App\Console\Commands\BiddingRule\cron. Start Cron.");
        Log::info($this->description);
        $CronDataArray = array(); // define array for Cron data Collection
        $responseData = BiddingRule::getBiddingRulesList();
        if ($responseData != FALSE) {
            foreach ($responseData as $singleArray) {
                $array = array();
                $array['fkBiddingRuleId'] = $singleArray->id;
                $array['sponsoredType'] = $singleArray->sponsoredType;
                $array['lookBackPeriodDays'] = $singleArray->lookBackPeriodDays;
                $array['frequency'] = $singleArray->frequency;
                $array['runStatus'] = 0;
                $array['isActive'] = 1;
                $array['currentRunTime'] = '0000-00-00 00:00:00';
                $array['lastRunTime'] = '0000-00-00 00:00:00';
                $array['nextRunTime'] = '0000-00-00 00:00:00';
                $array['createdAt'] = date('Y-m-d H:i:s');
                $array['updatedAt'] = date('Y-m-d H:i:s');
                array_push($CronDataArray, $array);
            }
            BiddingRule::storeBiddingRuleCron($CronDataArray);
        }//endif
        $responseDataCron = BiddingRule::getBiddingRuleCron();
        if ($responseDataCron != FALSE) {
            foreach ($responseDataCron as $singleData) {
                $id = $singleData->id;
                $runningStatusArray = BiddingRule::getBiddingRuleCron($id);
                if (!$runningStatusArray) { // in case rule not find value of bidding rule cron data
                    continue;
                }
                $currentRunStatus = $runningStatusArray->runStatus;
                $nextRunTimeRunCheck = $runningStatusArray->nextRunTime;
                $CronLastRunCheck = $runningStatusArray->lastRunTime;
                $lookBackPeriodDays = $runningStatusArray->lookBackPeriodDays;
                $sponsoredType = $runningStatusArray->sponsoredType;
                $fkBiddingRuleId = $runningStatusArray->fkBiddingRuleId;
                $frequency = $runningStatusArray->frequency;
                $currentTimeNow = date('Y-m-d H');
                $currentDayNow = date('Y-m-d');
                $hourlyCheckCurrentTimeNow = date('H');
                $CronTime = $runningStatusArray->currentRunTime;
                $hourlyCheckCronTime = '';
                if ($CronTime == '0000-00-00 00:00:00') {
                    $CronTime = date('Y-m-d');
                    $hourlyCheckCronTime = date('H');
                } else {
                    $CronTime = date('Y-m-d', strtotime($runningStatusArray->currentRunTime));
                    $hourlyCheckCronTime = date('H', strtotime($runningStatusArray->currentRunTime));
                }
                switch ($frequency) {
                    case "once_per_day":
                        $CronLastRun = $CronLastRunCheck;
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
                        $nextRunTime = $nextRunTimeRunCheck;
                        $nextRunTimeHourMinuteSec = '';
                        $TodayNextRun = '';
                        if ($nextRunTime == '0000-00-00 00:00:00') {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+1 day', time()));
                            $TodayNextRun = date('Y-m-d', time());
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                        } else {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime($nextRunTime));
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                            $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            // if next time is greater than last time
                            if ($CronLastRun > $nextRunTime) {
                                $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+1 day', time()));
                                $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                                $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            }
                        }
                        if ($CronLastRun < $nextRunTime && $currentRunStatus == 0 && $hourlyCheckCurrentTimeNow == $hourlyCheckCronTime && $TodayNextRun == $currentDayNow) {
                            $this->RunCronFrequencyVise($id, $lookBackPeriodDays, $sponsoredType, $fkBiddingRuleId, 1);
                        } elseif ($currentRunStatus == 1 && $CronTime < $currentDayNow) { // change cronRun status again 0
                            // tracker code
                            Log::info('start update bidding rule query for update CronRun status to 0');
                            $updateArray = array(
                                'updatedAt' => date('Y-m-d H:i:s'),
                                'runStatus' => '0',
                                'checkRule' => '0',
                                'ruleResult' => '0',
                                'emailSent' => '0',
                            );
                            BiddingRule::updateCronBiddingRuleStatus($id, $updateArray);
                            Log::info('end update bidding rule query for update CronRun status to 0');
                        }
                        break;
                    case "every_day":
                        $CronLastRun = $CronLastRunCheck;
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
                        $nextRunTime = $nextRunTimeRunCheck;
                        $nextRunTimeHourMinuteSec = '';
                        $TodayNextRun = '';
                        if ($nextRunTime == '0000-00-00 00:00:00') {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+2 day', time()));
                            $TodayNextRun = date('Y-m-d', time());
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                        } else {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime($nextRunTime));
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                            $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            // if next time is greater than last time
                            if ($CronLastRun > $nextRunTime) {
                                $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+2 day', time()));
                                $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                                $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            }
                        }

                        if ($CronLastRun < $nextRunTime && $currentRunStatus == 0 && $hourlyCheckCurrentTimeNow == $hourlyCheckCronTime && $TodayNextRun == $currentDayNow) {
                            $this->RunCronFrequencyVise($id, $lookBackPeriodDays, $sponsoredType, $fkBiddingRuleId, 2);
                        } elseif ($currentRunStatus == 1 && $CronTime < $currentDayNow) { // change cronRun status again 0
                            // tracker code
                            Log::info('start update bidding rule query for update CronRun status to 0');
                            $updateArray = array(
                                'updatedAt' => date('Y-m-d H:i:s'),
                                'runStatus' => '0',
                                'checkRule' => '0',
                                'ruleResult' => '0',
                                'emailSent' => '0',
                            );
                            BiddingRule::updateCronBiddingRuleStatus($id, $updateArray);
                            Log::info('end update bidding rule query for update CronRun status to 0');
                        }
                        break;
                    case "w":
                        $CronLastRun = $CronLastRunCheck;
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
                        $nextRunTime = $nextRunTimeRunCheck;
                        $nextRunTimeHourMinuteSec = '';
                        $TodayNextRun = '';
                        if ($nextRunTime == '0000-00-00 00:00:00') {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+7 day', time()));
                            $TodayNextRun = date('Y-m-d', time());
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                        } else {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime($nextRunTime));
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                            $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            // if next time is greater than last time
                            if ($CronLastRun > $nextRunTime) {
                                $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+7 day', time()));
                                $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                                $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            }
                        }
                        if ($CronLastRun < $nextRunTime && $currentRunStatus == 0 && $hourlyCheckCurrentTimeNow == $hourlyCheckCronTime && $TodayNextRun == $currentDayNow) {
                            $this->RunCronFrequencyVise($id, $lookBackPeriodDays, $sponsoredType, $fkBiddingRuleId, 7);
                        } elseif ($currentRunStatus == 1 && $CronTime < $currentDayNow) { // change cronRun status again 0
                            // tracker code
                            Log::info('start update bidding rule query for update CronRun status to 0');
                            $updateArray = array(
                                'updatedAt' => date('Y-m-d H:i:s'),
                                'runStatus' => '0',
                                'checkRule' => '0',
                                'ruleResult' => '0',
                                'emailSent' => '0',
                            );
                            BiddingRule::updateCronBiddingRuleStatus($id, $updateArray);
                            Log::info('end update bidding rule query for update CronRun status to 0');
                        }
                        break;
                    case "m":
                        $CronLastRun = $CronLastRunCheck;
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
                        $nextRunTime = $nextRunTimeRunCheck;
                        $nextRunTimeHourMinuteSec = '';
                        $TodayNextRun = '';
                        if ($nextRunTime == '0000-00-00 00:00:00') {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+30 day', time()));
                            $TodayNextRun = date('Y-m-d', time());
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                        } else {
                            $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime($nextRunTime));
                            $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                            $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            // if next time is greater than last time
                            if ($CronLastRun > $nextRunTime) {
                                $nextRunTimeHourMinuteSec = date('Y-m-d H:i:s', strtotime('+30 day', time()));
                                $nextRunTime = date('Y-m-d H', strtotime($nextRunTimeHourMinuteSec));
                                $TodayNextRun = date('Y-m-d', strtotime($nextRunTimeHourMinuteSec));
                            }
                        }
                        if ($CronLastRun < $nextRunTime && $currentRunStatus == 0 && $hourlyCheckCurrentTimeNow == $hourlyCheckCronTime && $TodayNextRun == $currentDayNow) {
                            $this->RunCronFrequencyVise($id, $lookBackPeriodDays, $sponsoredType, $fkBiddingRuleId, 30);
                        } elseif ($currentRunStatus == 1 && $CronTime < $currentDayNow) { // change cronRun status again 0
                            // tracker code
                            Log::info('start update bidding rule query for update CronRun status to 0');
                            $updateArray = array(
                                'updatedAt' => date('Y-m-d H:i:s'),
                                'runStatus' => '0',
                                'checkRule' => '0',
                                'ruleResult' => '0',
                                'emailSent' => '0',
                            );
                            BiddingRule::updateCronBiddingRuleStatus($id, $updateArray);
                            Log::info('end update bidding rule query for update CronRun status to 0');
                        }
                        break;
                    default:
                        // not found
                }// switch
                //Send alert notification
            }// end foreach
        }// endif

        Log::info("filePath:App\Console\Commands\BiddingRule\cron. End Cron.");
    }

    /**
     * This function is used to frequency Changes
     *
     * @param $id
     * @param $lookBackPeriodDays
     * @param $sponsoredType
     * @param $fkBiddingRuleId
     * @param $nextRunDay
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     * @throws \ReflectionException
     */
    private function RunCronFrequencyVise($id, $lookBackPeriodDays, $sponsoredType, $fkBiddingRuleId, $nextRunDay)
    {
        $DB1 = 'mysql'; // layer 0 database
        $updateArray = array(
            'currentRunTime' => date('Y-m-d H:i:s'),
            'lastRunTime' => date('Y-m-d H:i:s'),
            'nextRunTime' => date('Y-m-d H:i:s', strtotime('+' . $nextRunDay . ' day', time())),
            'updatedAt' => date('Y-m-d H:i:s'),
            'runStatus' => '1',
        );
        BiddingRule::updateCronBiddingRuleStatus($id, $updateArray);
        //Send alert notification for cron job start
        $notificationData = $this->cronJobNotificationData($fkBiddingRuleId, 'biddingRuleCronJobStarted');
        if (!empty($notificationData)) {
            $addNotification = new AmsAlertNotificationsController();
            $addNotification->addAlertNotification($notificationData);
        }
        Artisan::call('keywordData:bidding_rule');
        Artisan::call('keywordlist:amsKeywordlist' . ' ' . $fkBiddingRuleId);
        $excelFileArray = array(); //  create array for cvs file data managing
        $ruleResultStatus = false;
        $slug = '';
        if ($sponsoredType == 'sponsoredBrands') {
            $slug = 'SB';
        } elseif ($sponsoredType == 'sponsoredProducts') {
            $slug = 'SP';
        } else if ($sponsoredType == 'sponsoredDisplay') {
            $slug = 'SD';
        }
        if ($slug != 'SD') {
            // only use SP and SB keyword bid Values
            $campaignData = BiddingRule::getKeywordData($fkBiddingRuleId, '', '', $slug);
            if ($campaignData->isNotEmpty()) {
                foreach ($campaignData as $campaign) {
                    $profileId = $campaign->profileId;
                    $fkConfigId = $campaign->fkConfigId;
                    $client_id = $campaign->client_id;
                    $campaignId = $campaign->campaignId;
                    $state = $campaign->state;
                    $reportType = $campaign->reportType;
                    $listOfKeyword = BiddingRule::getKeywordData($fkBiddingRuleId, $campaignId, '', $slug);
                    $keywordIdArray = array(); // keyword ID string conversation
                    foreach ($listOfKeyword as $singleKeyword) {
                        $keywordIdArray[] = $singleKeyword->keywordId;
                    }//endforeach
                    $parameter = array(
                        $campaignId,
                        "'" . implode(',', $keywordIdArray) . "'",
                        $slug,
                        (int)$lookBackPeriodDays,
                        'keyword');
                    $KeywordReportDataSP = \DB::connection($DB1)->select("CALL spAMSTacos(?,?,?,?,?)", $parameter);
                    if (!empty($KeywordReportDataSP)) {
                        foreach ($KeywordReportDataSP as $singleData) {
                            BiddingRule::updateCronBiddingRuleStatus($id, array('checkRule' => '1', 'updatedAt' => date('Y-m-d H:i:s')));
                            $Rule = BiddingRule::getSpecificBiddingRule($fkBiddingRuleId);
                            if (!empty($Rule)) {
                                $bid = 0.02;
                                $bidValues = BiddingRule::getKeywordData($fkBiddingRuleId, $campaignId, $singleData->keywordId, $slug);
                                if (!empty($bidValues)) {
                                    $bid = $bidValues->bid;
                                }//endif
                                $adGroupId = $singleData->adGroupId;
                                $keywordId = $singleData->keywordId;
                                $impression = $singleData->impression;
                                $clicks = $singleData->clicks;
                                $cost = $singleData->cost;
                                $revenue = $singleData->revenue;
                                $acos = $singleData->acos;
                                $cpc = $singleData->cpc;
                                $roas = $singleData->roas;
                                $cpa = $singleData->cpa;
                                $fkProfileId = $singleData->fkProfileId;
                                $campaignName = $singleData->campaignName;
                                $conditionArray = array();
                                $metricList = explode(',', $Rule->metric);
                                $conditionList = explode(',', $Rule->condition);
                                $integerValuesList = explode(',', $Rule->integerValues);
                                for ($i = 0; $i < count($metricList); $i++) {
                                    $value = 0;
                                    if ($metricList[$i] == 'impression') {
                                        $value = $impression;
                                    } elseif ($metricList[$i] == 'clicks') {
                                        $value = $clicks;
                                    } elseif ($metricList[$i] == 'cost') {
                                        $value = $cost;
                                    } elseif ($metricList[$i] == 'revenue') {
                                        $value = $revenue;
                                    } elseif ($metricList[$i] == 'roas') {
                                        $value = $roas;
                                    } elseif ($metricList[$i] == 'acos') {
                                        $value = $acos;
                                    } elseif ($metricList[$i] == 'cpc') {
                                        $value = $cpc;
                                    } elseif ($metricList[$i] == 'cpa') {
                                        $value = $cpa;
                                    }
                                    $condition = '<'; // default less
                                    $and = '';
                                    if ($conditionList[$i] == 'greater') {
                                        $condition = '>';
                                    }//endif
                                    if ($Rule->andOr != 'NA') {
                                        if ($Rule->andOr == 'and') {
                                            $and = '&&';
                                        } else if ($Rule->andOr == 'or') {
                                            $and = '||';
                                        }//endif
                                    }//endif
                                    // Database value condition User input value, e.g (Store procedure values greater , less user input Values)
                                    $conditionText = '(' . $value . ' ' . $condition . ' ' . $integerValuesList[$i] . ')' . (($i == 1) ? '' : $and);
                                    array_push($conditionArray, $conditionText);
                                }// end for loop
                                $Result = eval('return (' . implode('', $conditionArray) . ');');
                                $ruleCheckStatus = 'FALSE'; // eval return true
                                $increaseBidValue = 0.02;
                                if ($Result) {
                                    $ruleResultStatus = TRUE;
                                    $updateArray = array(
                                        'ruleResult' => '1',
                                    );
                                    $ruleCheckStatus = 'TRUE';
                                    $bidBy = $Rule->bidBy;
                                    if ($Rule->thenClause == 'raise') {
                                        $increaseBidValue = round(abs((($bidBy / 100) * $bid) + $bid), 2);
                                    } else {
                                        if ($bidBy >= 0 && $bidBy <= 100) {
                                            $increaseBidValue = round(abs((($bidBy / 100) * $bid) - $bid), 2);
                                        } else {
                                            $increaseBidValue = round(abs(((100 / 100) * $bid) - $bid), 2);
                                        }//endif
                                    }//endif
                                    if ($increaseBidValue == 0.0) {
                                        $increaseBidValue = 0.02;
                                    }//endif
                                    $data['data'] = array(
                                        'profileId' => $profileId,
                                        'fkConfigId' => $fkConfigId,
                                        'clientId' => $client_id,
                                        'campaignId' => $campaignId,
                                        'adGroupId' => $adGroupId,
                                        'keywordId' => $keywordId,
                                        'state' => $state,
                                        'reportType' => $reportType,
                                        'oldbid' => $bid,
                                        'newbid' => $increaseBidValue,
                                        'fkBiddingRuleId' => $Rule->id,
                                        'biddingRuleName' => $Rule->ruleName,
                                        'sponsoredType' => $sponsoredType,
                                        'fkProfileId' => $fkProfileId,
                                        'campaignName' => $campaignName
                                    );
                                    Artisan::call('updateKeywordbid:updatebid', $data);
                                }// endif
                                $dataArray = array(
                                    'id' => $id,
                                    'updateArray' => $updateArray,
                                    'rule' => $Rule,
                                    'ruleCheckStatus' => $ruleCheckStatus,
                                    'arrayReportData' => $singleData,
                                    'bid' => $bid,
                                    'increaseBid' => $increaseBidValue
                                );
                                array_push($excelFileArray, $dataArray);
                            }// end if
                        }// end foreach
                    }//endif
                }// endforeach
                if ($ruleResultStatus) {// check if rule status true then sent email
                    $this->emailSent($excelFileArray, $slug);
                }//endif
            }//endif
        } elseif ($slug == 'SD') {
            // only use for target SD bidding rule
            $targetData = TargetList::with('getConfigId')
                ->where(['fkBiddingRuleId' => $fkBiddingRuleId])
                ->groupBy('campaignId')
                ->get();
            if ($targetData->isNotEmpty()) {
                foreach ($targetData as $target) {
                    $client_id = $target->getConfigId->client_id;
                    $profileId = $target->profileId;
                    $fkConfigId = $target->fkConfigId;
                    $campaignId = $target->campaignId;
                    $state = $target->state;
                    $reportType = $target->reportType;
                    $listOfTarget = TargetList::with('getConfigId')
                        ->where(['fkBiddingRuleId' => $fkBiddingRuleId])
                        ->where(['campaignId' => $campaignId])->get();
                    $targetIdArray = array(); // keyword ID string conversation
                    foreach ($listOfTarget as $singleTarget) {
                        $targetIdArray[] = $singleTarget->targetId;
                    }//endforeach
                    $parameter = array(
                        $campaignId,
                        "'" . implode(',', $targetIdArray) . "'",
                        '',
                        (int)$lookBackPeriodDays,
                        'target');
                    $targetReportDataSP = \DB::connection($DB1)->select("CALL spAMSTacos(?,?,?,?,?)", $parameter);
                    if (!empty($targetReportDataSP)) {
                        foreach ($targetReportDataSP as $singleData) {
                            BiddingRule::updateCronBiddingRuleStatus($id, array('checkRule' => '1', 'updatedAt' => date('Y-m-d H:i:s')));
                            $Rule = BiddingRule::getSpecificBiddingRule($fkBiddingRuleId);
                            if (!empty($Rule)) {
                                $bidValues = TargetList::with('getConfigId')
                                    ->where(['fkBiddingRuleId' => $fkBiddingRuleId])
                                    ->where(['campaignId' => $campaignId])
                                    ->where(['targetId' => $singleData->targetId])
                                    ->first();
                                $bid = 0.02;
                                if ($bidValues) {
                                    $bid = $bidValues->bid;
                                }
                                $adGroupId = $singleData->adGroupId;
                                $targetId = $singleData->targetId;
                                $impression = $singleData->impression;
                                $clicks = $singleData->clicks;
                                $cost = $singleData->cost;
                                $revenue = $singleData->revenue;
                                $acos = $singleData->acos;
                                $cpc = $singleData->cpc;
                                $roas = $singleData->roas;
                                $cpa = $singleData->cpa;
                                $fkProfileId = $singleData->fkProfileId;
                                $campaignName = $singleData->campaignName;
                                $conditionArray = array();
                                $metricList = explode(',', $Rule->metric);
                                $conditionList = explode(',', $Rule->condition);
                                $integerValuesList = explode(',', $Rule->integerValues);
                                for ($i = 0; $i < count($metricList); $i++) {
                                    $value = 0;
                                    if ($metricList[$i] == 'impression') {
                                        $value = $impression;
                                    } elseif ($metricList[$i] == 'clicks') {
                                        $value = $clicks;
                                    } elseif ($metricList[$i] == 'cost') {
                                        $value = $cost;
                                    } elseif ($metricList[$i] == 'revenue') {
                                        $value = $revenue;
                                    } elseif ($metricList[$i] == 'roas') {
                                        $value = $roas;
                                    } elseif ($metricList[$i] == 'acos') {
                                        $value = $acos;
                                    } elseif ($metricList[$i] == 'cpc') {
                                        $value = $cpc;
                                    } elseif ($metricList[$i] == 'cpa') {
                                        $value = $cpa;
                                    }
                                    $condition = '<'; // default less
                                    $and = '';
                                    if ($conditionList[$i] == 'greater') {
                                        $condition = '>';
                                    }
                                    if ($Rule->andOr != 'NA') {
                                        if ($Rule->andOr == 'and') {
                                            $and = '&&';
                                        } else if ($Rule->andOr == 'or') {
                                            $and = '||';
                                        }
                                    }
                                    // Database value condition User input value, e.g (Store procedure values greater , less user input Values)
                                    $conditionText = '(' . $value . ' ' . $condition . ' ' . $integerValuesList[$i] . ')' . (($i == 1) ? '' : $and);
                                    array_push($conditionArray, $conditionText);
                                }// end for loop

                                $Result = eval('return (' . implode('', $conditionArray) . ');');
                                $ruleCheckStatus = 'FALSE'; // eval return true
                                $increaseBidValue = 0.02;
                                if ($Result) {
                                    $ruleResultStatus = TRUE;
                                    $updateArray = array(
                                        'ruleResult' => '1',
                                    );
                                    $ruleCheckStatus = 'TRUE';
                                    $bidBy = $Rule->bidBy;
                                    if ($Rule->thenClause == 'raise') {
                                        $increaseBidValue = round(abs((($bidBy / 100) * $bid) + $bid), 2);
                                    } else {
                                        if ($bidBy <= 100 && $bidBy >= 0) {
                                            $increaseBidValue = round(abs((($bidBy / 100) * $bid) - $bid), 2);
                                        } else {
                                            $increaseBidValue = round(abs(((100 / 100) * $bid) - $bid), 2);
                                        }
                                    }
                                    if ($increaseBidValue == 0.0) {
                                        $increaseBidValue = 0.02;
                                    }
                                    $data['data'] = array(
                                        'profileId' => $profileId,
                                        'fkConfigId' => $fkConfigId,
                                        'clientId' => $client_id,
                                        'campaignId' => $campaignId,
                                        'adGroupId' => $adGroupId,
                                        'targetId' => $targetId,
                                        'state' => $state,
                                        'reportType' => $reportType,
                                        'oldbid' => $bid,
                                        'newbid' => $increaseBidValue,
                                        'fkBiddingRuleId' => $Rule->id,
                                        'biddingRuleName' => $Rule->ruleName,
                                        'sponsoredType' => $sponsoredType,
                                        'fkProfileId' => $fkProfileId,
                                        'campaignName' => $campaignName
                                    );
                                    Artisan::call('updateTargetbid:updatebid', $data);
                                } // endif
                                $dataArray = array(
                                    'id' => $id,
                                    'updateArray' => $updateArray,
                                    'rule' => $Rule,
                                    'ruleCheckStatus' => $ruleCheckStatus,
                                    'arrayReportData' => $singleData,
                                    'bid' => $bid,
                                    'increaseBid' => $increaseBidValue
                                );
                                array_push($excelFileArray, $dataArray);
                            }// end if
                        }//endforeach
                    }// end if
                }// endforeach
                if ($ruleResultStatus) {// check if rule status true then sent email
                    $this->emailSent($excelFileArray, $slug);
                }
            }
            Log::info('Target data not found.');
        }
        //bidding rule cron job ends
        $notificationData = $this->cronJobNotificationData($fkBiddingRuleId, 'biddingRuleCronJobCompleted');
        if (!empty($notificationData)) {
            $addNotification = new AmsAlertNotificationsController();
            $addNotification->addAlertNotification($notificationData);
        }
    }

    /**
     * This function is used to Sent Email to User
     *
     * @param $excelFileArray
     * @param $slug
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     * @throws \ReflectionException
     */
    private function emailSent($excelFileArray, $slug)
    {
        $id = $excelFileArray[0]['id']; // tbl_ams_bidding_rule_cron 'id'
        $emailStatus = BiddingRule::getCronBiddingRuleEmailStatus($id);
        if (!empty($emailStatus) && $emailStatus->emailSent == 0 && $emailStatus->ruleResult == 0) {
            $ruleCheckData = array();
            $ruleStatementData = array();
            BiddingRule::updateCronBiddingRuleStatus($id, array('ruleResult' => '1', 'updatedAt' => date('Y-m-d H:i:s')));
            foreach ($excelFileArray as $singleArray) {
                $conditionArray = array();
                $metricList = explode(',', $singleArray['rule']->metric);
                $conditionList = explode(',', $singleArray['rule']->condition);
                $integerValuesList = explode(',', $singleArray['rule']->integerValues);
                $conditionTextCsvData = '';
                for ($i = 0; $i < count($metricList); $i++) {
                    $metricValue = $metricList[$i];
                    if ($metricValue == 'cost') {
                        $metricValue = 'spend';
                    }
                    if ($metricValue == 'revenue') {
                        $metricValue = 'sales';
                    }
                    $and = '';
                    if ($singleArray['rule']->andOr != 'NA') {
                        if ($singleArray['rule']->andOr == 'and') {
                            $and = 'AND';
                        } else if ($singleArray['rule']->andOr == 'or') {
                            $and = 'OR';
                        }
                    }
                    $conditionTextCsvData .= ' if ' . $metricValue . ' ' . $conditionList[$i] . ' ' . $integerValuesList[$i] . ' ' . (($i == 1) ? '' : $and);
                    array_push($conditionArray, $conditionTextCsvData);
                }// end for loop
                $conditionTextCsvData .= 'Then ' . $singleArray['rule']->thenClause . ' bid by ' . $singleArray['rule']->bidBy . ' %';
                array_push($ruleStatementData, $conditionTextCsvData);
                if ($slug != 'SD') {
                    $ruleCheckDataArray = array(
                        "Campaign Id" => isset($singleArray['arrayReportData']->campaignId) ? $singleArray['arrayReportData']->campaignId : '0',
                        "Campaign Name" => isset($singleArray['arrayReportData']->campaignName) ? $singleArray['arrayReportData']->campaignName : 'NA',
                        "AdGroup Id" => isset($singleArray['arrayReportData']->adGroupId) ? $singleArray['arrayReportData']->adGroupId : '0',
                        "AdGroup Name" => isset($singleArray['arrayReportData']->adGroupName) ? $singleArray['arrayReportData']->adGroupName : 'NA',
                        "Keyword Id" => isset($singleArray['arrayReportData']->keywordId) ? $singleArray['arrayReportData']->keywordId : '0',
                        "Keyword Text" => isset($singleArray['arrayReportData']->keywordText) ? $singleArray['arrayReportData']->keywordText : 'NA',
                        "Match Type" => isset($singleArray['arrayReportData']->matchType) ? $singleArray['arrayReportData']->matchType : 'NA',
                        "Impression" => isset($singleArray['arrayReportData']->impression) ? $singleArray['arrayReportData']->impression : '0',
                        "Clicks" => isset($singleArray['arrayReportData']->clicks) ? $singleArray['arrayReportData']->clicks : '0',
                        "Spend" => isset($singleArray['arrayReportData']->cost) ? $singleArray['arrayReportData']->cost : '0',
                        "Sales" => isset($singleArray['arrayReportData']->revenue) ? $singleArray['arrayReportData']->revenue : '0',
                        "ROAS" => isset($singleArray['arrayReportData']->roas) ? $singleArray['arrayReportData']->roas : '0',
                        "ACOS" => isset($singleArray['arrayReportData']->acos) ? $singleArray['arrayReportData']->acos : '0',
                        "CPC" => isset($singleArray['arrayReportData']->cpc) ? $singleArray['arrayReportData']->cpc : '0',
                        "CPA" => isset($singleArray['arrayReportData']->cpa) ? $singleArray['arrayReportData']->cpa : '0',
                        "keywordBid" => isset($singleArray['bid']) ? $singleArray['bid'] : '0.0',
                        "Bid" => isset($singleArray['increaseBid']) ? $singleArray['increaseBid'] : '0.0',
                        "Check Status" => isset($singleArray['ruleCheckStatus']) ? $singleArray['ruleCheckStatus'] : 'NA',
                    );
                } else if ($slug == 'SD') {
                    $ruleCheckDataArray = array(
                        "Campaign Id" => isset($singleArray['arrayReportData']->campaignId) ? $singleArray['arrayReportData']->campaignId : '0',
                        "Campaign Name" => isset($singleArray['arrayReportData']->campaignName) ? $singleArray['arrayReportData']->campaignName : 'NA',
                        "AdGroup Id" => isset($singleArray['arrayReportData']->adGroupId) ? $singleArray['arrayReportData']->adGroupId : '0',
                        "AdGroup Name" => isset($singleArray['arrayReportData']->adGroupName) ? $singleArray['arrayReportData']->adGroupName : 'NA',
                        "Target Id" => isset($singleArray['arrayReportData']->targetId) ? $singleArray['arrayReportData']->targetId : '0',
                        "Targeting Text" => isset($singleArray['arrayReportData']->targetingText) ? $singleArray['arrayReportData']->targetingText : 'NA',
                        "Impression" => isset($singleArray['arrayReportData']->impression) ? $singleArray['arrayReportData']->impression : '0',
                        "Clicks" => isset($singleArray['arrayReportData']->clicks) ? $singleArray['arrayReportData']->clicks : '0',
                        "Spend" => isset($singleArray['arrayReportData']->cost) ? $singleArray['arrayReportData']->cost : '0',
                        "Sales" => isset($singleArray['arrayReportData']->revenue) ? $singleArray['arrayReportData']->revenue : '0',
                        "ROAS" => isset($singleArray['arrayReportData']->roas) ? $singleArray['arrayReportData']->roas : '0',
                        "ACOS" => isset($singleArray['arrayReportData']->acos) ? $singleArray['arrayReportData']->acos : '0',
                        "CPC" => isset($singleArray['arrayReportData']->cpc) ? $singleArray['arrayReportData']->cpc : '0',
                        "CPA" => isset($singleArray['arrayReportData']->cpa) ? $singleArray['arrayReportData']->cpa : '0',
                        "targetBid" => isset($singleArray['bid']) ? $singleArray['bid'] : '0.0',
                        "Bid" => isset($singleArray['increaseBid']) ? $singleArray['increaseBid'] : '0.0',
                        "Check Status" => isset($singleArray['ruleCheckStatus']) ? $singleArray['ruleCheckStatus'] : 'NA',
                    );
                }
                array_push($ruleCheckData, $ruleCheckDataArray);
            }// end foreach
            // define sheet and assign structure array of data
            $list = collect([[
                'Rule Name' => $excelFileArray[0]['rule']->ruleName,
                'Frequency' => $excelFileArray[0]['rule']->frequency,
                'Bidding Rule Conditions ' => $ruleStatementData[0],
                'Current Run Time' => $emailStatus->currentRunTime,
                'Next Run Time' => $emailStatus->nextRunTime
            ]]);
            // make sheets
            $sheets = new SheetCollection([
                'Rule Detail' => $list,
                'Rule Check Data' => $ruleCheckData
            ]);
            $fileName = $excelFileArray[0]['rule']->ruleName . '.xlsx';
            $fileNameWithPath = public_path('ams/bidding-rule/' . $fileName);
            (new FastExcel($sheets))->export($fileNameWithPath);
            $messages = array();
            $messages[0] = "<p>This email notification is to inform you about bidding rule.</p>";
            $messages[1] = "<p>Please see the attach file for further details.</p>";
            $bodyHTML = ((new BuyBoxEmailAlertMarkdown("Bidding Rule", $messages))->render());
            $data = [];
            $fkProfileId = $excelFileArray[0]['rule']
                ->profileId;
            $getBrandId = AccountModel::where('fkId', $fkProfileId)
                ->where('fkAccountType', 1)
                ->first();
            if (!empty($getBrandId)) {
                $brandId = $getBrandId->fkBrandId;
            } else {
                $brandId = '';
            }
            if (!empty($brandId) || $brandId != 0) {
                $getBrandAssignedUsers = ClientModel::with("brandAssignedUsersEmails")->find($brandId);
                $managerEmailArray = [];
                foreach ($getBrandAssignedUsers->brandAssignedUsersEmails as $getBrandAssignedUser) {
                    $brandAssignedUserId = $getBrandAssignedUser->pivot->fkManagerId;
                    $GetManagerEmail = User::where('id', $brandAssignedUserId)->first();
                    $managerEmailArray[] = $GetManagerEmail->email;
                }
                $data["toEmails"] = $managerEmailArray;
                if (isset($excelFileArray[0]['rule']->ccEmails) && $excelFileArray[0]['rule']->ccEmails != '') {
                    $cc = explode(',', $excelFileArray[0]['rule']->ccEmails);
                    $data["cc"] = $cc;
                }

                $data["subject"] = "Bidding Rule";
                $data["bodyHTML"] = $bodyHTML;
                $data["attachments"] = array(
                    array(
                        "path" => $fileNameWithPath,
                        "name" => $fileName
                    ),
                );
                $responseEmail = SendMailViaPhpMailerLib($data);
                if (empty($responseEmail['errors'])) {
                    $id = $excelFileArray[0]['id']; // tbl_ams_bidding_rule_cron 'id'
                    BiddingRule::updateCronBiddingRuleStatus($id, array('emailSent' => '1', 'updatedAt' => date('Y-m-d H:i:s')));
                    if (file_exists($fileNameWithPath)) {
                        unlink($fileNameWithPath);
                    }
                }
            } else {
                // if email and rule checked
                Log::info('brandId is missing.');
            }
        } else {
            // if email and rule checked
            Log::info('Email and Rule checked.');
        }
        sleep(2);
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function cronJobNotificationData($fkBiddingRuleId, $type)
    {
        $getBiddngRuleName = BiddingRule::getSpecificBiddingRule($fkBiddingRuleId);
        $notificationData = [];
        if (!empty($getBiddngRuleName)) {
            switch ($type) {
                case "biddingRuleCronJobStarted":
                    $notificationTitle = "Bidding Rule Cron Job Started.";
                    $notificationMessage = "Bidding Rule name: " . $getBiddngRuleName->ruleName . " started";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    break;
                case "biddingRuleCronJobCompleted":
                    $notificationTitle = "Bidding Rule Cron Job Completed.";
                    $notificationMessage = "Bidding Rule name: " . $getBiddngRuleName->ruleName . " completed";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    break;
            }
            $biddingRuleProfileId = $getBiddngRuleName->profileId;
            $fkProfileId = explode('|', $biddingRuleProfileId);
            $notificationData['type'] = $type;
            $notificationData['moduleName'] = "bidding rule";
            $notificationData['notificationTitle'] = $notificationTitle;
            $notificationData['notificationMessage'] = $notificationMessage;
            $notificationData['biddingRuleName'] = $getBiddngRuleName->ruleName;
            $notificationData['fkProfileId'] = $fkProfileId[0];
            $notificationData['sendEmail'] = 1;
        }
        return $notificationData;
    }
}
