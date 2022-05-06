<?php

namespace App\Console\Commands\Ams\Portfolio;

use App\Models\AccountModels\AccountModel;
use App\Models\AMSModel;
use App\Models\ClientModels\ClientModel;
use App\Models\DayPartingModels\DayPartingCampaignScheduleIds;
use App\Models\DayPartingModels\DayPartingPortfolioScheduleIds;
use App\Models\DayPartingModels\PfCampaignSchedule;
use App\Models\DayPartingModels\DayPartingHistoryCronSchedules;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use App\User;
use Artisan;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;

class updateAllCampaignList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateAllCampaignList:portfolio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to update All Campaign List';

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
     *
     */
    public function handle()
    {
        $allScheduleCampaignsPf = PfCampaignSchedule::select('id', 'scheduleName', 'portfolioCampaignType',
            'endDate', 'startDate', 'emailReceiptStart', 'emailReceiptEnd', 'ccEmails', 'isScheduleExpired',
            'stopScheduleDate', 'isCronRunning', 'created_at', 'fkProfileId')
            ->where('isScheduleExpired', 0)
            ->where('isActive', 1)
            ->with('sponsoredBrand:id,name,campaignId,profileId,fkConfigId', 'sponsoredDisplay:id,name,campaignId,profileId,fkConfigId', 'sponsoredProduct:id,name,campaignId,profileId,fkConfigId', 'timeCampaigns:fkScheduleId,day,startTime,endTime')
            ->get();
        Log::info("filePath:Commands\Ams\Portfolio\updateAllCampaignList. Start Cron.");
        Log::info($this->description);
        if (!$allScheduleCampaignsPf->isEmpty()) {
            foreach ($allScheduleCampaignsPf as $singleRecord) {

                Log::info("Loop start for " . $singleRecord->scheduleName);

                $currentDate = date('Y-m-d');
                $startDate = $singleRecord->startDate;
                $expireDate = $singleRecord->endDate;

                // If current date less than expire date AND current date => star date
                if ($currentDate >= $startDate) {

                    if ($currentDate <= $expireDate || is_null($expireDate)) {
                        $stopScheduleDate = $singleRecord->stopScheduleDate;
                        if (is_null($stopScheduleDate) || $currentDate == $stopScheduleDate) {

                            $todayName = strtolower(date('l'));

                            $enableCampaignList = $this->getEnablePauseCampaignDataOne($singleRecord, 'enabled');
                            $pauseCampaignList = $this->getEnablePauseCampaignDataOne($singleRecord, 'paused');

                            $scheduleTimings = $singleRecord->timeCampaigns;

                            if (!$scheduleTimings->isEmpty()) {
                                foreach ($scheduleTimings as $singleTiming) {
                                    $cronDay = $singleTiming['day'];
                                    switch ($todayName) {
                                        case 'monday':
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }

                                            break;
                                        }
                                        case 'tuesday':
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }

                                            break;
                                        }
                                        case "wednesday":
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }

                                            break;
                                        }
                                        case "thursday":
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }

                                            break;
                                        }
                                        case "friday":
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }
                                            break;
                                        }
                                        case "saturday":
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }
                                            break;
                                        }
                                        case "sunday":
                                        {
                                            if ($todayName == $cronDay) {
                                                Log::info($todayName . ' value = ' . $cronDay);
                                                $this->checkTimingForEachDay($singleTiming, $enableCampaignList, $pauseCampaignList, $singleRecord);
                                            }
                                            break;
                                        }
                                        default:
                                        {
                                            Log::info('no days are selected for this schedule');
                                            break;
                                        }
                                    }
                                }

                            }
                        }
                    } else {
                        $expireData['isScheduleExpired'] = 1;
                        PfCampaignSchedule::updateSchedule($singleRecord->id, $expireData);
                        Log::info('Schedule week completed. Name = ' . $singleRecord->scheduleName);
                        switch ($singleRecord->portfolioCampaignType) {
                            case 'Campaign':
                            {
                                DayPartingCampaignScheduleIds::where('fkScheduleId', $singleRecord->id)
                                    ->update([
                                        'userSelection' => 1,
                                        'enablingPausingTime' => '23:59:00',
                                        'enablingPausingStatus' => 'expired'
                                    ]);
                                break;
                            }
                            case 'Portfolio':
                            {
                                DayPartingPortfolioScheduleIds::where('fkScheduleId', $singleRecord->id)->update([
                                    'userSelection' => 1,
                                    'enablingPausingTime' => '23:59:00',
                                    'enablingPausingStatus' => 'expired'
                                ]);
                                DayPartingCampaignScheduleIds::where('fkScheduleId', $singleRecord->id)->update([
                                    'userSelection' => 1,
                                    'enablingPausingTime' => '23:59:00',
                                    'enablingPausingStatus' => 'expired'
                                ]);
                                break;
                            }
                        }// Switch Case End
                    }
                } else {
                    Log::info(' Schedule Date Does not come yet');
                }
                Log::info("Loop End for " . $singleRecord->scheduleName);
            }
        } else {
            Log::info("DayParting No Campaigns To Run");
        }
        Log::info("filePath:Commands\Ams\Portfolio\updateAllCampaignList. End Cron.");
    }

    /**
     * @param $scheduleId
     * @param $startTime
     * @param $endTime
     * @param null $message
     * @return mixed
     */
    private function campaignScheduleHistoryArray($scheduleId, $startTime, $endTime, $message = NULL)
    {
        $campaignScheduleHistory['fkScheduleId'] = $scheduleId;
        $campaignScheduleHistory['startTime'] = $startTime;
        $campaignScheduleHistory['isMessage'] = $message;
        $campaignScheduleHistory['cronDate'] = date('Y-m-d');

        return $campaignScheduleHistory;
    }

    /**
     * @param $checkTiming
     * @param $enableCampaignList
     * @param $pauseCampaignList
     * @param $singleRecord
     */
    private function checkTimingForEachDay($checkTiming, $enableCampaignList, $pauseCampaignList, $singleRecord)
    {
        $startTime = $checkTiming['startTime'];
        $endTime = $checkTiming['endTime'];
        if (strlen($startTime) > 8 && strlen($endTime) > 8) {
            $explodeStartTiming = explode(',', $startTime);
            $explodeEndTiming = explode(',', $endTime);
            $count = count($explodeStartTiming);
            for ($i = 0; $i < $count; $i++) {
                $startActualTime = date("H:i:s", strtotime($explodeStartTiming[$i]));
                $endActualTime = date("H:i:s", strtotime($explodeEndTiming[$i]));
                $this->checkScheduleTimings($startActualTime, $endActualTime, $enableCampaignList, $pauseCampaignList, $singleRecord);
            }
        } else {

            $startActualTime = date("H:i:s", strtotime($startTime));
            $endActualTime = date("H:i:s", strtotime($endTime));
            $this->checkScheduleTimings($startActualTime, $endActualTime, $enableCampaignList, $pauseCampaignList, $singleRecord);

        }
    }

    private function checkScheduleTimings($startTime, $endTime, $enableCampaignList, $pauseCampaignList, $singleRecord)
    {
        $response = TRUE;
        $currentTime = date('H:i:00');
        $managerEmailArray = $this->getEmailManagers($singleRecord->fkProfileId);
         //if (TRUE) {
        Log::info(" Day Parting check start time = " . $startTime . " currentTime = " . $currentTime);
        // if start time equal to current Time, current time greater than start time

        if ($startTime === $currentTime) {
            Log::info(" Day Parting start Time Matches ");
            $return = $this->enablePauseScheduleCampaigns($enableCampaignList);

            if ($return == TRUE) {
                $scheduleData = $this->scheduleUpdateStatuses(1, 1, 0, 0);
                PfCampaignSchedule::updateSchedule($singleRecord->id, $scheduleData);
                $campaignScheduleHistory = [];
                $campaignScheduleHistory = $this->campaignScheduleHistoryArray($singleRecord->id, $startTime, $endTime);
                $campaignScheduleHistory['endTime'] = NULL;
                $campaignScheduleHistory['creationDate'] = date('Y-m-d h:i:s');
                $campaignScheduleHistory['updationDate'] = date('Y-m-d h:i:s');
                DayPartingHistoryCronSchedules::insert($campaignScheduleHistory);

                if ($singleRecord->emailReceiptStart == 1) {
                    Log::info('cronEnablingPausingActiveDays  = Email enabled on start time against schedule Name  = ' . $singleRecord->scheduleName);
                    if (!empty($managerEmailArray)) {
                        _sendEmailForEnabledCampaign($managerEmailArray, $singleRecord->ccEmails, $singleRecord->scheduleName);
                    }
                }
                $response = TRUE;
            } else {
                $scheduleData = $this->scheduleUpdateStatuses(0, 0, 1, 0);
                PfCampaignSchedule::updateSchedule($singleRecord->id, $scheduleData);
                $campaignScheduleHistory = [];
                $campaignScheduleHistory['fkScheduleId'] = $singleRecord->id;
                $campaignScheduleHistory['startTime'] = $startTime;
                $campaignScheduleHistory['endTime'] = NULL;
                $campaignScheduleHistory['isMessage'] = 'Error has occured while enabling campaigns';
                $campaignScheduleHistory['cronDate'] = date('Y-m-d');
                $campaignScheduleHistory['creationDate'] = date('Y-m-d h:i:s');
                $campaignScheduleHistory['updationDate'] = date('Y-m-d h:i:s');
                DayPartingHistoryCronSchedules::insert($campaignScheduleHistory);
                if (!empty($managerEmailArray)) {
                    _sendEmailForErrorCampaign($managerEmailArray, $singleRecord->ccEmails, $singleRecord->scheduleName);
                }
                $response = FALSE;
            }
        }

        //      if (TRUE) {
        Log::info(" Day Parting check end time = " . $endTime . " currentTime = " . $currentTime);
        if ($endTime === $currentTime) {
            $return = $this->enablePauseScheduleCampaigns($pauseCampaignList);
            Log::info(" Day Parting End Time Matches ");

            if ($return == TRUE) {

                $scheduleData = $this->scheduleUpdateStatuses(0, 1, 0, 1);
                PfCampaignSchedule::updateSchedule($singleRecord->id, $scheduleData);
                $campaignScheduleHistory = [];
                $campaignScheduleHistory['fkScheduleId'] = $singleRecord->id;
                $campaignScheduleHistory['endTime'] = $endTime;
                $campaignScheduleHistory['isMessage'] = NULL;
                $campaignScheduleHistory['cronDate'] = date('Y-m-d');
                $campaignScheduleHistory['updationDate'] = date('Y-m-d h:i:s');
                $existingHistory = DayPartingHistoryCronSchedules::where('startTime', $startTime)
                    ->where('cronDate', date('Y-m-d'))
                    ->where('fkScheduleId', $singleRecord->id)
                    ->first();
                DayPartingHistoryCronSchedules::where('startTime', $existingHistory->startTime)
                    ->where('fkScheduleId', $singleRecord->id)
                    ->where('cronDate', $existingHistory->cronDate)
                    ->update($campaignScheduleHistory);
                if ($singleRecord->emailReceiptEnd == 1) {
                    Log::info('cronEnablingPausingActiveDays  = Email enabled on start time against schedule Name  = ' . $singleRecord->scheduleName);
                    if (!empty($managerEmailArray)) {
                        _sendEmailForPausedCampaign($managerEmailArray, $singleRecord->ccEmails, $singleRecord->scheduleName);
                    }
                }
                $response = TRUE;
            } else {
                $scheduleData = $this->scheduleUpdateStatuses(0, 0, 1, 0);
                PfCampaignSchedule::updateSchedule($singleRecord->id, $scheduleData);
                $campaignScheduleHistory = [];
                $campaignScheduleHistory['fkScheduleId'] = $singleRecord->id;
                $campaignScheduleHistory['endTime'] = $endTime;
                $campaignScheduleHistory['isMessage'] = 'An Occured while pausing Campaign';
                $campaignScheduleHistory['cronDate'] = date('Y-m-d');
                $campaignScheduleHistory['updationDate'] = date('Y-m-d h:i:s');
                $existingHistory = DayPartingHistoryCronSchedules::where('startTime', $startTime)
                    ->where('fkScheduleId', $singleRecord->id)
                    ->where('cronDate', date('Y-m-d'))
                    ->first();
                DayPartingHistoryCronSchedules::where('startTime', $existingHistory->startTime)
                    ->where('cronDate', $existingHistory->cronDate)
                    ->where('fkScheduleId', $singleRecord->id)
                    ->update($campaignScheduleHistory);
            }
        }

        return $response;
    }

    private function setCampaignArray($singleCampaign, $state, $url)
    {
        $apiVarData['campaignId'] = intval($singleCampaign->campaignId);
        $apiVarData['campaignName'] = $singleCampaign->name;
        $apiVarData['profileId'] = intval($singleCampaign->profileId);
        $apiVarData['fkConfigId'] = intval($singleCampaign->fkConfigId);
        $apiVarData['state'] = $state;
        $apiVarData['url'] = $url;

        return $apiVarData;
    }

    /**
     * @param $recordSchedule
     * @param $state
     * @return array
     */
    private function getEnablePauseCampaignDataOne($recordSchedule, $state)
    {
        $apiVarDataToSend = [];
        // Urls For All Campaigns
        $apiUrl = getApiUrlForDiffEnv(env('APP_ENV'));
        $sponsoredBrandUrl = $apiUrl . '/' . Config::get('constants.sbCampaignUrl');
        $sponsoredProductUrl = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spCampaignUrl');
        $sponsoredDisplayUrl = $apiUrl . '/' . Config::get('constants.sdCampaignUrl');
        $scheduleId = $recordSchedule->id;
        $scheduleName = $recordSchedule->scheduleName;
        $fkProfileId = $recordSchedule->fkProfileId;
        $sbCampaign = $recordSchedule->sponsoredBrand;
        if (!$sbCampaign->isEmpty()) {
            foreach ($sbCampaign as $singleCampaign) {
                $apiVarData = $this->setCampaignArray($singleCampaign, $state, $sponsoredBrandUrl);
                $apiVarData['scheduleId'] = $scheduleId;
                $apiVarData['scheduleName'] = $scheduleName;
                $apiVarData['fkProfileId'] = $fkProfileId;
                array_push($apiVarDataToSend, $apiVarData);
            }
        }

        $spCampaign = $recordSchedule->sponsoredProduct;
        if (!$spCampaign->isEmpty()) {
            foreach ($spCampaign as $singleCampaign) {
                $apiVarData = $this->setCampaignArray($singleCampaign, $state, $sponsoredProductUrl);
                $apiVarData['scheduleId'] = $scheduleId;
                $apiVarData['scheduleName'] = $scheduleName;
                $apiVarData['fkProfileId'] = $fkProfileId;
                array_push($apiVarDataToSend, $apiVarData);
            }
        }

        $sdCampaign = $recordSchedule->sponsoredDisplay;
        if (!$sdCampaign->isEmpty()) {
            foreach ($sdCampaign as $singleCampaign) {
                $apiVarData = $this->setCampaignArray($singleCampaign, $state, $sponsoredDisplayUrl);
                $apiVarData['scheduleId'] = $scheduleId;
                $apiVarData['scheduleName'] = $scheduleName;
                $apiVarData['fkProfileId'] = $fkProfileId;
                array_push($apiVarDataToSend, $apiVarData);
            }
        }
        return $apiVarDataToSend;
    }

    /**
     * @param $postData
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function enablePauseScheduleCampaigns($postData)
    {
        if (!empty($postData)) {
            Log::info("Auth token get from DB Start updateAllCampaignList DayParting!");
            $postCount = count($postData);
            $storeDataArrayUpdate = [];
            $try = 0;
            for ($i = 0; $i < $postCount; $i++) {
                $scheduleData = $postData[$i];
                $fkConfigId = $postData[$i]['fkConfigId'];
                // Get Access Token
                b:
                $obAccessToken = new AMSModel();
                $dataAccessTakenData = $obAccessToken->getParameterAndAuthById($fkConfigId);
                if ($dataAccessTakenData != FALSE && !empty($dataAccessTakenData)) {
                    $clientId = $dataAccessTakenData->client_id;
                    $accessToken = $dataAccessTakenData->access_token;

                    // Making Array to send over PUT Call
                    $apiPostDataToSend = [];
                    $apiPostDataToSend[] = [
                        'campaignId' => $postData[$i]['campaignId'],
                        'state' => $postData[$i]['state']
                    ];


                    try {
                        $client = new Client();
                        // Header
                        $headers = [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'Amazon-Advertising-API-ClientId' => $clientId,
                            'Amazon-Advertising-API-Scope' => $postData[$i]['profileId']
                        ];
                        Log::info('Url Day Parting Campaigns = ' . $postData[$i]['url']);
                        Log::info('Url Day Parting Post Data ' . json_encode($apiPostDataToSend));
                        $response = $client->request('PUT', $postData[$i]['url'], [
                            'headers' => $headers,
                            'body' => json_encode($apiPostDataToSend),
                            'delay' => Config::get('constants.delayTimeInApi'),
                            'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                            'timeout' => Config::get('constants.timeoutInApi')
                        ]);

                        $responseBody = json_decode($response->getBody()->getContents());

                        Log::info('Day Parting Campaign Id' . $postData[$i]['campaignId'] . 'Response = ' . json_encode($responseBody));
                        if (!empty($responseBody) && !is_null($responseBody)) {
                            $storeDataArray = [];
                            $storeDataArray['campaignId'] = $responseBody[0]->campaignId;
                            $storeDataArray['state'] = $postData[$i]['state'];
                            $storeDataArray['updatedAt'] = date("Y-m-d H:i:s");
                            array_push($storeDataArrayUpdate, $storeDataArray);
                        }
                    } catch (\Exception $ex) {
                        $try += 1;
                        if ($try >= 3) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                            $try = 0;
                            continue;
                        }
                        $errorCode = $ex->getCode();
                        $errorMessage = $ex->getMessage();
                        $notificationData = $this->notificationData($scheduleData, $errorCode);
                        $notificationData['errorMessage'] = $errorMessage;
                        $addNotification = new AmsAlertNotificationsController();
                        if ($errorCode == 401) {
                            if (strstr($errorMessage, '401 Unauthorized')) { // if auth token expire
                                if (strstr($errorMessage, 'Not authorized to access scope')) {
                                    // store profile list not valid
                                    Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                } elseif (strstr($errorMessage, 'No matching advertiser found for scope')) {
                                    Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                                    if ($try == 1) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                } else {
                                    $authCommandArray = array();
                                    $authCommandArray['fkConfigId'] = $fkConfigId;
                                    \Artisan::call('getaccesstoken:amsauth', $authCommandArray);
                                    if ($try == 2) {
                                        $addNotification->addAlertNotification($notificationData);
                                    }
                                    goto b;
                                }
                            } elseif (strstr($errorMessage, 'advertiser found for scope')) {
                                // store profile list not valid
                                Log::info("Invalid Profile Id: " . $postData[$i]['profileId']);
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                            }
                        } else if ($errorCode == 429) { //https://advertising.amazon.com/API/docs/v2/guides/developer_notes#Rate-limiting
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 502) {
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 503) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '503');
                            sleep(Config::get('constants.sleepTime') + 5);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 400) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '400');
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 403) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '403');
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 404) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '404');
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 422) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '422');
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else if ($errorCode == 500) {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '500');
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        } else {
                            AMSModel::insertTrackRecord('Ams\Portfolio\updateAllCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, $errorCode);
                            sleep(Config::get('constants.sleepTime') + 2);
                            if ($try == 1) {
                                $addNotification->addAlertNotification($notificationData);
                            }
                            goto b;
                        }

                        Log::error($errorMessage);
                    }// End catch
                } else {
                    Log::info("AMS client Id or access token not found Ams\Portfolio\updateAllCampaignList.");
                }
            } // End For Loop
            // Update Campaign Records
            Log::info('Day Parting storeDataArrayUpdate ' . json_encode($storeDataArrayUpdate));
            if (!empty($storeDataArrayUpdate)) {
                Log::info('Day Parting storeDataArrayUpdate not empty ');
                PortfolioAllCampaignList::updateCampaign($storeDataArrayUpdate);
                return TRUE;
            } else {
                Log::info('Day Parting storeDataArrayUpdate not empty ');
                return FALSE;

            }
        } else {
            Log::info("No Post Data in Campaigns");
            return FALSE;
        }
    }

    /**
     * @param $isCronRunning
     * @param $isCronSuccess
     * @param $isCronError
     * @param $isCronEnd
     * @return mixed
     */
    private function scheduleUpdateStatuses($isCronRunning, $isCronSuccess, $isCronError, $isCronEnd)
    {
        $scheduleData['isCronRunning'] = $isCronRunning;
        $scheduleData['isCronSuccess'] = $isCronSuccess;
        $scheduleData['isCronError'] = $isCronError;
        $scheduleData['isCronEnd'] = $isCronEnd;

        return $scheduleData;
    }

    /**
     * @param $fkProfileId
     * @return array
     */
    function getEmailManagers($fkProfileId)
    {
        $GetManagerId = AccountModel::where('fkId', $fkProfileId)->where('fkAccountType', 1)->first();
        $brandId = '';
        if (!empty($GetManagerId)) {
            $brandId = $GetManagerId->fkBrandId;
        }

        $managerEmailArray = [];
        if (!empty($brandId) || $brandId != 0) {
            $getBrandAssignedUsers = ClientModel::with("brandAssignedUsersEmails")->find($brandId);
            foreach ($getBrandAssignedUsers->brandAssignedUsersEmails as $getBrandAssignedUser) {
                $brandAssignedUserId = $getBrandAssignedUser->pivot->fkManagerId;
                $GetManagerEmail = User::where('id', $brandAssignedUserId)->first();
                $managerEmailArray[] = $GetManagerEmail->email;
            }
        }
        return $managerEmailArray;
    }

    /**
     *   deleteAmsFailedLinkReportId
     * @param $data
     * @param $errorCode
     * @return $array
     */
    private function notificationData($data, $errorCode)
    {
        $notificationData = $data;
        $notificationData['moduleName'] = "day parting";
        $notificationData['errorType'] = $errorCode;
        $notificationData['notificationTitle'] = "Day Parting Error";
        $notificationData['notificationMessage'] = "Error " . $errorCode . " triggered on state : " . $data['state'];
        $notificationData['sendEmail'] = 1;
        $notificationData['type'] = $errorCode;
        return $notificationData;
    }

}
