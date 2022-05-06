<?php

namespace App\Console\Commands\Ams\Portfolio;

use App\Models\AMSModel;
use App\Models\DayPartingModels\DayPartingCampaignScheduleIds;
use App\Models\DayPartingModels\PfCampaignSchedule;
use App\Models\DayPartingModels\PortfolioAllCampaignList;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Artisan;
use App\Libraries\AmsAlertNotifications\AmsAlertNotificationsController;

class pauseCampaignListDayParting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pauseCampaignListDayParting:portfolio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to pause or enable Campaign List of Day parting';

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        Log::info("filePath:Commands\Ams\Portfolio\pauseCampaignListDayParting. Start Cron.");
        Log::info($this->description);
        $pauseEnableCampaign = PfCampaignSchedule::select('id', 'scheduleName', 'portfolioCampaignType', 'isScheduleExpired', 'fkProfileId')
            ->with('pauseCampaignPivotForCrons:id,name,campaignId,profileId,state,fkScheduleId,fkConfigId,campaignType')
            ->get();

        if (!$pauseEnableCampaign->isEmpty()) {
            foreach ($pauseEnableCampaign as $singleRecord) {
                $currentTime = date('H:i:00');
                $pauseCron = $singleRecord->pauseCampaignPivotForCrons;
                if (!$pauseCron->isEmpty()) {
                    foreach ($pauseCron as $campaign) {

                        $cronTime = $campaign->pivot->enablingPausingtime;
                        $userSelection = $campaign->pivot->userSelection;
                        Log::info('Check Cron Timing Pause Campaign to Run On Campaigns');
                        Log::info('Current Time pauseCampaignlist =' . $currentTime);
                        Log::info('Cron Time pauseCampaignlist =' . $cronTime);
                        if ($userSelection == 1 || $userSelection == 2) {
                            Log::info('User selected option=>' . $userSelection . 'pausing campaigns');
                            $campaignList = $this->getEnablePauseCampaign($campaign, 'paused');
                        } elseif ($userSelection == 3) {
                            Log::info('User selected option=>' . $userSelection . 'enabling campaigns permanently');
                            $campaignList = $this->getEnablePauseCampaign($campaign, 'enabled');
                        }
                        //if (TRUE) {
                        $campaignList['scheduleId'] = $singleRecord->id;
                        $campaignList['scheduleName'] = $singleRecord->scheduleName;
                        $campaignList['fkProfileId'] = $singleRecord->fkProfileId;
                        if ($currentTime === $cronTime) {
                            Log::info('Current Time =' . $currentTime . ' Matches Cron time =' . $cronTime);
                            $result = $this->pausingEnablingCampaigns($campaignList);
                            if ($result == TRUE) {
                                Log::info('pauseCampaignListDayParting:portfolio = Enabling Pausing Campaigns Return .' . $result);
                                Log::info('pauseCampaignListDayParting:portfolio = update Pivot table');

                                DayPartingCampaignScheduleIds::where('fkScheduleId', $singleRecord->id)
                                    ->where('fkCampaignId', $campaignList['id'])
                                    ->where('enablingPausingTime', '!=', NULL)
                                    ->update([
                                        'isEnablingPausingDone' => 1
                                    ]);
                            }
                        }
                    } // End foreach loop
                }
            }
        }
        Log::info("filePath:Commands\Ams\Portfolio\pauseCampaignListDayParting. End Cron.");
    }

    /**
     * @param $singleRecord
     * @param $url
     * @return array
     */
    private function getEnablePauseCampaign($campaignRecord, $state)
    {
        $apiVarData = [];
        $apiUrl = getApiUrlForDiffEnv(env('APP_ENV'));
        $sponsoredBrandUrl = $apiUrl . '/' . Config::get('constants.sbCampaignUrl');
        $sponsoredProductUrl = $apiUrl . '/' . Config::get('constants.apiVersion') . '/' . Config::get('constants.spCampaignUrl');
        $sponsoredDisplayUrl = $apiUrl . '/' . Config::get('constants.sdCampaignUrl');

        if (!empty($campaignRecord)) {
            $apiVarData['campaignId'] = intval($campaignRecord['campaignId']);
            $apiVarData['campaignName'] = $campaignRecord->name;
            $apiVarData['profileId'] = intval($campaignRecord['profileId']);
            $apiVarData['fkConfigId'] = intval($campaignRecord['fkConfigId']);
            $apiVarData['id'] = intval($campaignRecord['id']);
            $apiVarData['state'] = $state;
            if ($campaignRecord['campaignType'] == 'sponsoredBrand') {
                $apiVarData['url'] = $sponsoredBrandUrl;
            }

            if ($campaignRecord['campaignType'] == 'sponsoredProducts') {
                $apiVarData['url'] = $sponsoredProductUrl;
            }
            if ($campaignRecord['campaignType'] == 'sponsoredDisplay') {
                $apiVarData['url'] = $sponsoredDisplayUrl;
            }
        }


        return $apiVarData;
    }

    /**
     * @param $postData
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function pausingEnablingCampaigns($postData)
    {
        $try = 0;
        if (!empty($postData)) {
            Log::info("pauseCampaignListDayParting = Auth token get from DB Start!");
            $storeDataArrayUpdate = [];
            $fkConfigId = $postData['fkConfigId'];
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
                    'campaignId' => $postData['campaignId'],
                    'state' => $postData['state']
                ];

                Log::info(env('APP_ENV') . ' Url -> ' . $postData['url']);

                try {
                    $client = new Client();
                    // Header
                    $headers = [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'Amazon-Advertising-API-ClientId' => $clientId,
                        'Amazon-Advertising-API-Scope' => $postData['profileId']
                    ];

                    $response = $client->request('PUT', $postData['url'], [
                        'headers' => $headers,
                        'body' => json_encode($apiPostDataToSend),
                        'delay' => Config::get('constants.delayTimeInApi'),
                        'connect_timeout' => Config::get('constants.connectTimeOutInApi'),
                        'timeout' => Config::get('constants.timeoutInApi')
                    ]);

                    $responseBody = json_decode($response->getBody()->getContents());

                    if (!empty($responseBody) && !is_null($responseBody)) {
                        $storeDataArray = [];
                        $storeDataArray['campaignId'] = $responseBody[0]->campaignId;
                        $storeDataArray['state'] = $postData['state'];
                        $storeDataArray['updatedAt'] = date("Y-m-d H:i:s");
                        array_push($storeDataArrayUpdate, $storeDataArray);
                    }
                    // Update Campaign Records
                    if (!empty($storeDataArrayUpdate)) {
                        PortfolioAllCampaignList::updateCampaign($storeDataArrayUpdate);
                        return TRUE;
                    } else {
                        return FALSE;
                    }
                } catch (\Exception $ex) {
                    $try += 1;
                    if ($try >= 3) {
                        AMSModel::insertTrackRecord('Ams\Portfolio\pause campaign list profile id:' . $postData['profileId'] . ' and number try is: ' . $try . ' error code :' . $ex->getCode(), 'success');
                        return FALSE;
                    }
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    $notificationData = $this->notificationData($postData, $errorCode);
                    $notificationData['errorMessage'] = $errorMessage;
                    $addNotification = new AmsAlertNotificationsController();
                    if ($errorCode == 401) {
                        if (strstr($errorMessage, '401 Unauthorized')) { // if auth token expire
                            if (strstr($errorMessage, 'Not authorized to access scope')) {
                                // store profile list not valid
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                                Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                            } elseif (strstr($errorMessage, 'No matching advertiser found for scope')) {
                                Log::info("Invalid Profile Id: " . json_encode($errorMessage));
                                if ($try == 1) {
                                    $addNotification->addAlertNotification($notificationData);
                                }
                            } else {
                                Log::error('Refresh Access token. In file filePath:Commands\Ams\Portfolio\pauseCampaignListDayParting');
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
                            Log::info("Invalid Profile Id: " . $postData['profileId']);
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
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData['profileId'] . ' and number try is: ' . $try, '503');
                        sleep(Config::get('constants.sleepTime') + 5);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else if ($errorCode == 400) {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '400');
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else if ($errorCode == 403) {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '403');
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else if ($errorCode == 404) {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '404');
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else if ($errorCode == 422) {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '422');
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else if ($errorCode == 500) {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, '500');
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    } else {
                        AMSModel::insertTrackRecord('Commands\Ams\Portfolio\pauseCampaignList. profile id:' . $postData[$i]['profileId'] . ' and number try is: ' . $try, $errorCode);
                        sleep(Config::get('constants.sleepTime') + 2);
                        if ($try == 1) {
                            $addNotification->addAlertNotification($notificationData);
                        }
                        goto b;
                    }
                    Log::error($errorMessage);
                }// End catch
            } else {
                Log::info("AMS client Id or access token not found Ams\Portfolio\pauseCampaignList.");
            }
        } else {
            Log::info("No Post Data in Campaigns");
            return FALSE;
        }
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
