<?php

namespace App\Console\Commands\bidMultiplier;

use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;
use App\Models\BidMultiplierModels\BidMultiplierListModel;
use App\Models\Tacos\keywordList;
use App\Models\BidMultiplierModels\Cron as bidMultiplierCronModel;
use App\Models\Tacos\TacosModel;
use App\Models\Tacos\TargetList;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Cron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:bidMultiplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is used to run and validate campaign every time check its active status.';

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
        bidMultiplierCronModel::updateRecord();
        $dataList = BidMultiplierListModel::with('campaign:type,campaignType,profileId,fkConfigId,fkProfileId,campaignId')
            ->where('isActive', 1)
            ->get();
        if ($dataList->isNotEmpty()) {
            foreach ($dataList as $list) {
                if ($list->campaign == null) {
                    continue;
                }// if campaign info empty
                $existCron = bidMultiplierCronModel::where('fkMultiplierId', $list->id)->get();
                if ($existCron->isNotEmpty()) {
                    bidMultiplierCronModel::where('fkMultiplierId', $list->id)
                        ->update([
                            'type' => $list->campaign->type,
                            'profileId' => $list->campaign->profileId,
                            'fkConfigId' => $list->campaign->fkConfigId,
                            'campaignId' => $list->campaign->campaignId,
                            'sponsoredType' => $list->campaign->campaignType,
                            'isActive' => $list->isActive
                        ]);
                } else {
                    $bidValuesObject = new bidMultiplierCronModel();
                    $bidValuesObject->fkMultiplierId = $list->id;
                    $bidValuesObject->type = $list->campaign->type;
                    $bidValuesObject->profileId = $list->campaign->profileId;
                    $bidValuesObject->fkConfigId = $list->campaign->fkConfigId;
                    $bidValuesObject->sponsoredType = $list->campaign->campaignType;
                    $bidValuesObject->campaignId = $list->campaign->campaignId;
                    $bidValuesObject->isActive = 1;
                    $bidValuesObject->runStatus = 0;
                    $bidValuesObject->currentRunTime = '0000-00-00 00:00:00';
                    $bidValuesObject->lastRunTime = '0000-00-00 00:00:00';
                    $bidValuesObject->createdAt = date('Y-m-d H:i:s');
                    $bidValuesObject->updatedAt = date('Y-m-d H:i:s');
                    $bidValuesObject->save();
                } // if already Tacos id inserted
            }//endforeach
        }//endif
        $responseDataCron = bidMultiplierCronModel::with('getTokenDetail')
            ->where('isActive', 1)
            ->get(); //get all record from DB
        if ($responseDataCron->isNotEmpty()) {
            foreach ($responseDataCron as $singleDataArray) {
                if ($singleDataArray->getTokenDetail == null) {
                    continue;
                }// if campaign info empty jump on another
                $singleData = bidMultiplierCronModel::with('getTokenDetail')
                    ->where('isActive', 1)
                    ->where('id', $singleDataArray->id)
                    ->first(); //get one record from DB
                if (empty($singleData)) {
                    continue; //jump on another
                }// if campaign info empty
                $fkMultiplierId = $singleData->fkMultiplierId;
                $CronTime = $singleData->currentRunTime;
                $bidMultiplierActualRecord = BidMultiplierListModel::find($fkMultiplierId);
                if (empty($bidMultiplierActualRecord)) {
                    continue; //jump on another
                }// if $bidMultiplier info empty
                // next cron run time
                $startDate = date('Y-m-d', strtotime($bidMultiplierActualRecord->startDate));
                $nextRunTime = date('Y-m-d', strtotime($bidMultiplierActualRecord->endDate));
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
                // manage cron time
                $finishTime = date('Y-m-d H:i:s', strtotime($nextRunTime . "11:59 p.m."));
                if ($singleData->runStatus == 0 && $nextRunTime >= $startDate && $startDate == date('Y-m-d') && $finishTime > date('Y-m-d H:i:s')) {
                    //Send alert notification for cron job start
                    $notificationData = $this->cronJobNotificationData($singleData, 'bidMultiplierCronJobStarted');
                    if (!empty($notificationData)) {
                        $addNotification = new AmsAlertNotificationsController();
                        $addNotification->addAlertNotification($notificationData);
                    }
                    $this->EnableCallForBidMulitplier($singleData);
                    //Bid Multiplier cron job ends
                    $notificationData = $this->cronJobNotificationData($singleData, 'bidMultiplierCronJobCompleted');
                    if (!empty($notificationData)) {
                        $addNotification = new AmsAlertNotificationsController();
                        $addNotification->addAlertNotification($notificationData);
                    }
                } elseif ($singleData->runStatus == 1 && $finishTime < date('Y-m-d H:i:s')) {
                    //Send alert notification for cron job start
                    $notificationData = $this->cronJobNotificationData($singleData, 'bidMultiplierCronJobStarted');
                    if (!empty($notificationData)) {
                        $addNotification = new AmsAlertNotificationsController();
                        $addNotification->addAlertNotification($notificationData);
                    }
                    $this->DisableCallForBiddingMultiplier($singleData);
                    //Bid Multiplier cron job ends
                    $notificationData = $this->cronJobNotificationData($singleData, 'bidMultiplierCronJobCompleted');
                    if (!empty($notificationData)) {
                        $addNotification = new AmsAlertNotificationsController();
                        $addNotification->addAlertNotification($notificationData);
                    }
                }
            }// end foreach
        }// endif
    }

    /**
     * @param $dataArray
     */
    private function EnableCallForBidMulitplier($dataArray)
    {
        ob_start();
        $data['data'] = $dataArray;
        $fkMultiplierId = $dataArray->fkMultiplierId;
        $updateArray = array(
            'currentRunTime' => date('Y-m-d H:i:s'),
            'lastRunTime' => date('Y-m-d H:i:s'),
            'runStatus' => '1',
        );
        bidMultiplierCronModel::where('fkMultiplierId', $fkMultiplierId)->update($updateArray);
        Artisan::call('update:bidMultiplier', $data);
        ob_end_flush();
    }

    /**
     * @param $dataArray
     */
    private function DisableCallForBiddingMultiplier($dataArray)
    {
        ob_start();
        $fkMultiplierId = $dataArray->fkMultiplierId;
        $updateArray = array(
            'updatedAt' => date('Y-m-d H:i:s'),
            'runStatus' => 3
        );
        bidMultiplierCronModel::where('fkMultiplierId', $fkMultiplierId)->update($updateArray);
        $data['data'] = $dataArray;
        Artisan::call('delete:bidMultiplier', $data);
        ob_end_flush();
    }

    /**
     *   notificationData
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function cronJobNotificationData($singleData, $type)
    {
        $profileId = $singleData->profileId;
        $fkMultiplierId = $singleData->fkMultiplierId;
        $campaignId = $singleData->campaignId;
        $getNotificationFkProfileId = getNotificationFkProfileId($profileId);
        $fkProfileId = $getNotificationFkProfileId->id;
        $notificationData = [];
        if (!empty($getNotificationFkProfileId)) {
            switch ($type) {
                case "bidMultiplierCronJobStarted":
                    $notificationTitle = "Bid Multiplier Cron Job Started.";
                    $notificationMessage = "Bid Multiplier id: " . $fkMultiplierId . " started";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    break;
                case "bidMultiplierCronJobCompleted":
                    $notificationTitle = "Bid Multiplier Cron Job Completed.";
                    $notificationMessage = "Bid Multiplier id: " . $fkMultiplierId . " completed";
                    $notificationData['time'] = date('Y-m-d H:i:s');
                    break;
            }
            $notificationData['type'] = $type;
            $notificationData['moduleName'] = "Bid Multiplier";
            $notificationData['notificationTitle'] = $notificationTitle;
            $notificationData['notificationMessage'] = $notificationMessage;
            $notificationData['fkMultiplierId'] = $fkMultiplierId;
            $notificationData['fkProfileId'] = $fkProfileId;
            $notificationData['sendEmail'] = 1;
        }
        return $notificationData;
    }
}
